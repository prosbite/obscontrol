# Image-Only Lower Third Template

## Summary
Add a new `image-only` lower third template. Creation/editing accepts name, subtitle, file upload (image), and width. When displayed on the overlay, only the uploaded image appears with fade animation. No text overlay.

## Design Decisions

| Concern | Decision |
|---|---|
| Template key | `image-only` |
| Display | Only `<img>` — name/subtitle stored but not rendered |
| Position | Same as other lower thirds: `bottom: 50px; left: 60px` |
| Default width | `10vw`, height `auto` |
| Animation | Opacity fade 0.4s in / 0.3s out; existing templates keep slide-in |
| File upload | File picker → `POST /api/upload` → path stored in `image` column |
| Empty image | Nothing renders (component returns null/gated by v-if) |
| Image storage | `storage/app/public/lowerthirds/`, accessible via `/storage/lowerthirds/...` |

## Implementation Tasks (ordered)

### 1. Database Migration
Create migration to add `width` column to `lower_thirds` table:
- Column: `$table->string('width')->nullable()->default('10vw')`

### 2. Backend — Model
`app/Models/LowerThird.php`:
- Add `'width'` to `$fillable`
- Add `'width' => 'string'` to `casts()`

### 3. Backend — Form Requests
**`StoreLowerThirdRequest.php`**:
- Add `'image-only'` to template `in:` rule: `'in:classic,minimal,banner,image-only'`
- Add `'width' => ['nullable', 'string', 'max:20']`

**`UpdateLowerThirdRequest.php`**:
- Same two changes as above.

### 4. Backend — Resource
**`LowerThirdResource.php`**:
- Add `'width' => $this->width` to the response array.

### 5. Backend — Upload Endpoint
New file `app/Http/Controllers/Api/UploadController.php`:
- Single method `__invoke(Request $request)`
- Validate: `image` file required, mimes: jpg/jpeg/png/gif/webp/svg, max 2048 KB
- Store to `public` disk in `lowerthirds/` directory
- Return JSON: `{ path: '/storage/lowerthirds/filename.ext' }`

**`routes/api.php`**:
- Inside `Route::middleware(['web', 'auth'])` group, after existing routes:
- `Route::post('/upload', \App\Http\Controllers\Api\UploadController::class);`

### 6. Frontend — TypeScript Type
**`resources/js/types/graphics.ts`**:
- Add `width: string | null` to the `LowerThird` interface.

### 7. Frontend — ImageOnly Display Component
New file `resources/js/Display/LowerThirdDesigns/ImageOnly.vue`:
- Props: `image: string | null`, `width: string | null`
- Template: `<img :src="image" :style="{ width: width || '10vw', height: 'auto' }" />` wrapped in a div
- Scoped styles for the wrapper (no extra padding, transparent bg)
- Does NOT render name or subtitle

### 8. Frontend — Overlay (Transition + Registration)
**`resources/js/Display/LowerThirdOverlay.vue`**:
- Import `ImageOnly` from `./LowerThirdDesigns/ImageOnly.vue`
- Add to `designMap`: `'image-only': ImageOnly`
- Add computed `transitionName`: `'lt-fade'` when template is `image-only`, `'lt'` otherwise
- Change `<Transition name="lt">` → `<Transition :name="transitionName">`
- Add scoped CSS for `.lt-fade-enter-active` / `.lt-fade-leave-active` (opacity transitions)
- Pass `:width="lt.width"` to the component

### 9. Frontend — Preview Component
**`resources/js/Components/sidebar/LowerThirds/LowerThirdPreview.vue`**:
- Add `width?: string | null` prop
- Add `v-else-if="template === 'image-only'"` block showing a small image thumbnail
- When image is null, show a gray placeholder with camera icon
- Style: match existing 64px height preview convention

### 10. Frontend — Dashboard Form
**`resources/js/Pages/Control/Dashboard.vue`**:
- Add `image-only` to `designs` array: `{ value: 'image-only', label: 'Image Only', desc: 'Uploaded image with fade animation' }`
- Add `width` field to `ltForm` ref defaults (value: `'10vw'`)
- When `ltForm.template === 'image-only'`, show:
  - **File input** (not URL text field) with accept `image/*`
  - On file change: upload via `POST /api/upload` using `FormData`, set response path to `ltForm.image`
  - Show upload progress/state (loading indicator during upload)
  - Preview thumbnail of uploaded image
  - **Width input**: `<input v-model="ltForm.width" placeholder="10vw" />`
- Remove the `v-if="ltForm.template === 'banner'"` conditional from the old image URL field, replace with template-conditional logic:
  - `image-only` → file input
  - `banner` → URL text input
  - others → no image input
- Include `width` in the `saveLt` payload: `width: ltForm.value.width || '10vw'`

### 11. Validation / Edge Cases
- **Server**: File type + size validated in UploadController
- **Client**: Upload failure → toast error; width defaults to `10vw` if empty
- **Storage link**: Requires `php artisan storage:link` (already configured in `config/filesystems.php`)
- **Run**: `php artisan migrate` after creating the migration

## Verification
1. Run migration: `php artisan migrate`
2. Ensure storage link exists: `php artisan storage:link`
3. Create a new `image-only` lower third via dashboard, upload an image
4. Show it from the dashboard — image fades in at bottom-left at configured width
5. Hide it — image fades out
6. Edit the width, show again — image resizes correctly
7. Create without uploading an image, show it — nothing appears (no error)
8. Existing classic/minimal/banner templates still work as before
