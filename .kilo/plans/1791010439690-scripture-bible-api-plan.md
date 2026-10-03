# Scripture Display + Bible API Integration Plan

## Goal

Replace manual scripture entry with a fast reference lookup: an operator types something like
`John 3:16-18` (or `Ps 23`, `1 Cor 13:4-7`), the app fetches the passage from an online Bible
API, caches it, and pushes it to the OBS display overlay. Operators can also save frequently used
passages to a reusable library.

## Decisions Locked

- **Provider**: `API.Bible` primary (full modern catalog: NIV, NKJV, NLT, NASB, CSB, etc.) with
  `bible-api.com` as a zero-config public-domain fallback (KJV/WEB/ASV). Implemented behind a
  `BibleProvider` interface so providers can be swapped/added without touching callers.
- **Bible Gateway is NOT usable**: it has no public API and its Terms of Use forbid automated
  scraping. Do not integrate it.
- **Parser**: full standard reference set — book names + common abbreviations, single verse,
  verse ranges, whole chapters, cross-chapter ranges, and multiple references separated by `;`
  or `,`. Canonical output uses USFM book IDs (shared by both providers).
- **Persistence**: a transient `bible_passages` cache (default 7-day TTL, hard max 30 days for
  compliance) plus the existing `scriptures` table kept as an explicit "Saved" library.
- **Display (interim)**: one simple overlay design — bottom, semi-transparent dark bar, body text
  centered, `BOOK CH:V (TRANSLATION)` emphasized in an accent color at the bottom-right. Templates
  and long-passage slide splitting are deferred (see Out of Scope).
- **Attribution**: translation abbreviation (e.g. `(NIV)`) shown on the overlay reference line;
  full copyright text + hyperlink to `https://api.bible` in a "Copyright & Attributions" area in
  the control dashboard.

## Compliance Constraints (API.Bible Starter / free)

These shape the code and must not be skipped:

- Cached API.Bible content must be refreshed at least every 30 days → cache TTL default **7 days**.
- Starter plan requires a visible citation and hyperlink to `https://api.bible` in the app UI.
- Each quoted passage must show the translation abbreviation; a linked full-copyright citation must exist.
- Non-commercial use only (a church with no ads/fees qualifies; minor tithe/donation links exempt).
- NKJV: Starter allowed only for non-commercial apps with < 5,000 monthly users.
- API key must stay server-side; never expose it to the browser.
- Do not modify/alter fetched scripture text; do not sub-license or bulk-download.
- FUMS (Fair Use Management System) analytics may be mandatory for web apps — see Open Items.

## Architecture / Data Flow

```
Control Dashboard (Vue)
  → GET /api/bible/passages?reference=John+3:16-18&translation=NIV
    → ReferenceParser (canonical USFM ranges)
    → BibleService: cache lookup (bible_passages)
        hit & fresh  → return cached
        miss/stale   → BibleProvider->passage() → upsert cache → return
  → operator clicks Show
    → POST /api/control/scripture/show-reference  {reference,text,translation}
      → GraphicsState.activeScripture + scriptureVisible=true
      → GraphicsEvent('ScriptureShown') broadcast (Reverb)
Display Client (OBS) ScriptureOverlay renders from store
```

## Reference Parser Spec

`App\Services\Bible\ReferenceParser` (server-side, pure, unit-tested).

Accept (case-insensitive, optional periods/spaces):
- `John 3:16` → `JHN.3.16`
- `John 3:16-18` → `JHN.3.16-JHN.3.18`
- `Psalm 23` / `Ps 23` → `PSA.23`
- `John 3:16-4:2` → `JHN.3.16-JHN.4.2`
- `Jn 3:16`, `1 Cor 13:4`, `1Cor 13:4-7`, `Song of Solomon 2:1`
- Multiple: `John 3:16; Rom 5:8` and verse lists `John 3:16,17` (comma + bare number continues the
  current chapter; `;` or comma + new book-like token starts a new reference).

Disambiguation rules:
- A comma followed by only digits → additional verses in the current chapter (expand to ranges).
- A comma followed by a letter / book token → new reference.
- `;` always separates references.
- Single-chapter books (`Jude`, `Obadiah`, `Philemon`, `2 John`, `3 John`): `Jude 1` means verse 1;
  use explicit `Jude 1:1` semantics for whole book only when clearly intended.

Output: a list of `PassageReference` DTOs (book USFM id, chapter, verseStart, verseEnd,
chapterEnd, verseEndEnd, canonical label). Multi-reference / verse-list inputs may require more
than one provider call; providers merge results into one `BiblePassage`.

Book metadata: `config/bible_books.php` returning all 66 books keyed by USFM id with
`name`, `aliases[]`, `chapters`, `testament`. Also includes a normalization map for common
abbreviations (`Jn`, `Ps`, `Gen`, `1 Cor`, `Rev`, etc.). `config/bible.php` includes it.

## Database Changes

New migration `create_bible_passages_table`:
- `id`, `provider` (string), `translation` (string), `reference_key` (string, unique) =
  `{provider}:{translation}:{canonical_ref}`, `label` (string), `text` (longText),
  `translation_abbr` (string, nullable), `copyright` (text, nullable), `verses` (json, nullable),
  `fetched_at` (timestamp), timestamps. Index on `(provider, translation)`.

New migration `add_source_fields_to_scriptures_table` (nullable, backward-compatible):
- `provider` (string, nullable), `translation_abbr` (string, nullable),
  `canonical_reference` (string, nullable), `fetched_at` (timestamp, nullable).

## Backend Tasks

1. `config/bible.php` + `config/bible_books.php`; add `api_bible` block to
   `config/services.php` (key/base URL) or keep all under `config/bible.php`. Add env entries to
   `.env.example`: `BIBLE_PROVIDER`, `API_BIBLE_KEY`, `API_BIBLE_DEFAULT_BIBLE_ID`,
   `BIBLE_API_DEFAULT_TRANSLATION`, `BIBLE_CACHE_TTL_DAYS`.
2. DTOs: `App\Services\Bible\PassageReference`, `BiblePassage`, `BibleTranslation`;
   `BibleException`.
3. `App\Services\Bible\BibleProvider` interface:
   `translations(): array`, `passage(string $translation, PassageReference $ref): BiblePassage`.
4. `ApiBibleProvider`:
   - Base `https://api.scripture.api.bible/v1`, auth header `api-key: {key}`.
   - `GET /bibles?language=eng` → translations (cached via Laravel cache, e.g. 24h).
   - `GET /bibles/{bibleId}/passages/{passageId}?content-type=text` where `passageId` is the
     canonical id (`JHN.3.16-JHN.3.18`, `PSA.23`). Verify exact query params
     (`include-verse-numbers`, `include-notes`, `include-titles`) against docs during
     implementation; strip markup/verse numbers to clean display text.
   - Parse `data.reference`, `data.content`, `data.copyright` (or fetch from `/bibles/{id}`).
5. `BibleApiComProvider`:
   - Base `https://bible-api.com`; `GET /{reference}?translation={id}` returns
     `{reference, text, verses[], translation_name, ...}`.
   - `translations()` returns the static public-domain list (kjv, web, asv, bbe, darby, ylt).
6. `ReferenceParser` per spec above.
7. `BibleService` (orchestration): pick provider (config), normalize, check `bible_passages`
   cache, enforce TTL (< 30 days; refresh if `fetched_at` older than `BIBLE_CACHE_TTL_DAYS`),
   upsert, and on provider failure fall back to `BibleApiComProvider`; if that fails and a stale
   cache row exists (< 30 days) return it, else throw.
8. `BibleController` (auth, `routes/api.php`):
   - `GET /api/bible/translations` → cached translation list.
   - `GET /api/bible/passages?reference=...&translation=...` → validate, parse, fetch/cache,
     return `BiblePassageResource` (label, text, translation_abbr, copyright, verses).
   - `POST /api/bible/passages/save` → persist into `scriptures` (reuse `StoreScriptureRequest`,
     extended to accept new nullable fields) and return `ScriptureResource`.
9. `ControlController::showScriptureReference(Request)` — validate
   `{reference, text, translation(optional)}`, set `activeScripture` + `scriptureVisible=true`,
   dispatch `ScriptureShown`. Route:
   `POST /api/control/scripture/show-reference`. Keep existing id-based `showScripture`.
10. Extend `ScriptureResource` with `provider`, `translation_abbr`, `canonical_reference`
    (nullable) and `BiblePassageResource` for live lookups.

## Frontend Tasks

1. `resources/js/types/graphics.ts`: add `BibleTranslation`, `BiblePassage`; extend `Scripture`
   with optional `provider`, `translation_abbr`.
2. `resources/js/Stores/graphics.ts`: add `showScripturePassage(payload)` calling
   `/api/control/scripture/show-reference` and `sync(data)`.
3. `resources/js/Pages/Control/Dashboard.vue` — redesign the `scriptures` tab:
   - Reference text input (Enter triggers lookup), translation `<select>` (default from config),
     Lookup button with loading/error state.
   - Preview card (label, text, abbreviation) with `Show`, `Save to library` actions.
   - Saved scriptures list (existing data) below, unchanged behavior.
   - "Copyright & Attributions" area: full copyright text + hyperlink to `https://api.bible`
     when an API.Bible translation is active.
4. `resources/js/Display/ScriptureOverlay.vue` — restyle per interim design:
   - Bottom, horizontally full-width, semi-transparent dark bar (`rgba(0,0,0,0.6)`-ish), body text
     centered with readable size, reference in accent color, bold, bottom-right, including
     `(TRANSLATION_ABBR)` when present. Preserve existing enter/leave transition. Keep all text
     inside the 1920×1080 transparent stage with no scrollbars.

## Failure Modes

- Missing/invalid API key → try `bible-api.com`; if the requested translation is unavailable there,
  return `422` with a clear message ("Modern translation requires API.Bible key").
- Provider 5xx / timeout / rate limit → served from cache if fresh; else stale cache (< 30 days);
  else `503` with retry message. Display remains synchronized via Reverb regardless.
- Unparseable reference → `422` with a helpful example.
- Never block the display client on an external API call.

## Validation Plan (Pest)

- Unit `ReferenceParserTest`: table-driven inputs → canonical references (aliases, abbreviations,
  ranges, whole chapters, cross-chapter, multi-reference, verse lists, single-chapter books,
  malformed input throws).
- Feature `BibleProviderTest` with `Http::fake()`: API.Bible response mapping (reference, text,
  abbreviation, copyright); error → fallback to bible-api.com.
- Feature `BiblePassageCacheTest`: second identical lookup makes no HTTP call; stale (`fetched_at`
  beyond TTL) triggers refresh; fallback returns stale row on provider failure.
- Feature `ScriptureControlTest`: `show-reference` requires auth, sets state, dispatches
  `ScriptureShown`.
- Run `php artisan test` (Pest), `vendor/bin/pint`, and `npm run build`.

## Out of Scope (Deferred)

- Overlay templates / multiple display designs (user will design later).
- Long-passage slide splitting / pagination / auto-scroll. This plan renders the fetched passage
  as one text block; operators should keep ranges reasonable until slides are designed.
- Queue integration for scripture (queue currently supports only `lowerthird` and `lyrics`).
- Bulk Bible download / offline full-Bible storage (prohibited by API.Bible and unnecessary).

## Open Items

- Confirm whether API.Bible's FUMS analytics requirement applies and how to implement it for this
  web app (contact API.Bible support). Not a code blocker but a compliance follow-up.
- Operator chooses which 3 copyrighted Bibles to enable on the API.Bible account and the default
  translation (config value).
```
