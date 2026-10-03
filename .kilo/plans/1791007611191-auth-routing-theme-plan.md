# Auth Routing Cleanup + Dark Login Theme

## Goal

1. Restyle the login/auth screens to match the app's dark theme (`bg-gray-950` shell, `bg-gray-900` cards, `border-gray-800`, indigo accents).
2. Remove the Breeze "Dashboard" page/route entirely; the Control page becomes the post-login home.
3. Lock the app down: everything except the display page and the auth flow requires login. Guests are sent to the login screen.
4. Add a slim top bar to `MainLayout` with app name, user name, Profile, and Log Out (because Control currently has no logout path).

## Locked Decisions

- **Public routes**: only `/display/main`, `/login`, forgot/reset-password, email-verification, and `/confirm-password` (all guest/auth-flow routes). Root `/` redirects guests to `/login` and authenticated users to `/control`.
- **Remove Welcome + Register**: delete `Welcome.vue`, `Register.vue`, `RegisteredUserController`, register routes, and registration test.
- **Theme scope**: restyle `GuestLayout` dark so all auth screens match; apply dark overrides on the auth pages' shared Breeze input components.
- **Logout placement**: slim top bar inside `MainLayout` (used by Control), with Profile link; Profile keeps `AuthenticatedLayout`.
- **No dashboard fallback**: `/dashboard` is deleted, not redirected.

## Key Facts / Constraints

- Tailwind is **v3** (`resources/css/app.css` uses `@tailwind base/components/utilities`, `tailwind.config.js` exists), so the `!` important prefix (e.g. `!bg-gray-800`) works for overriding hardcoded Breeze component classes.
- Breeze form components (`TextInput.vue`, `InputLabel.vue`, `InputError.vue`, `Checkbox.vue`, `PrimaryButton.vue`) hardcode light classes with no props. They fall through extra `class`, but that is **additive**, so dark styling requires `!` overrides at usage sites.
- `User` does **not** implement `MustVerifyEmail` (`app/Models/User.php:5` commented). The `/dashboard` `verified` middleware is therefore inert; removing the route has no verification behavior change.
- `AuthenticatedSessionController.php:36` already redirects to `control.dashboard`. Other auth controllers still point at `dashboard` (see task B2).
- `AuthenticatedLayout.vue` (used only by Profile) hard-links `dashboard` 4× — must be repointed or Profile breaks.
- Seeded admin: `gret@admin.com` / `admin` (`database/seeders/DatabaseSeeder.php:15`).
- Display page calls public `/api/control/state` and public Echo channel `graphics`; no auth needed. Leave as-is.

## Backend Tasks

### B1. `bootstrap/app.php`
Inside `->withMiddleware(...)` add:
```php
$middleware->redirectGuestsTo(fn () => route('login'));
$middleware->redirectUsersTo('/control');
```
`redirectUsersTo` replaces the framework default `dashboard → home → /` lookup in `RedirectIfAuthenticated` (vendor `.../Auth/Middleware/RedirectIfAuthenticated.php:63`), which would otherwise degrade to `/` after `dashboard` is removed.

### B2. Repoint all `route('dashboard')` references to `route('control.dashboard')`
- `app/Http/Controllers/Auth/ConfirmablePasswordController.php:39`
- `app/Http/Controllers/Auth/EmailVerificationNotificationController.php:17`
- `app/Http/Controllers/Auth/EmailVerificationPromptController.php:19`
- `app/Http/Controllers/Auth/VerifyEmailController.php:18` and `:25` (keep the `.'?verified=1'` suffix)
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php:36` already correct.

### B3. `routes/web.php`
- Replace the `Welcome` root closure with:
  ```php
  Route::get('/', function () {
      return auth()->check()
          ? redirect()->route('control.dashboard')
          : redirect()->route('login');
  });
  ```
- Delete the `/dashboard` route (`web.php:17-19`).
- Remove the now-unused `Illuminate\Foundation\Application` import (keep `Inertia` for `/control` and `/display/main`).
- Leave `/display/main` public and `/control` + `/profile` under `auth`.

### B4. `routes/auth.php`
- Remove the `register` GET/POST routes (`:15-18`) and the `RegisteredUserController` import.
- Keep login/logout/forgot/reset/verify/confirm routes unchanged.

### B5. Delete dead files
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `resources/js/Pages/Welcome.vue`
- `resources/js/Pages/Dashboard.vue`
- `resources/js/Pages/Auth/Register.vue`

## Frontend Tasks

### F1. `resources/js/Components/sidebar/Layout/MainLayout.vue` — add slim top bar
Rewrite to a vertical flex shell:
- Root: `flex h-screen flex-col bg-gray-950 text-white`.
- Header: `flex items-center justify-between border-b border-gray-800 bg-gray-900 px-6 py-3`.
  - Left: app name (`import.meta.env.VITE_APP_NAME || 'Control'`).
  - Right: current user name from `usePage().props.auth.user?.name`, a `<Link :href="route('profile.edit')">Profile</Link>`, and a `<Link :href="route('logout')" method="post" as="button">Log Out</Link>`, styled with `text-gray-400 hover:text-white text-sm`.
- Keep `<main class="flex-1 overflow-auto"><slot /></main>`.
- Use `<script setup lang="ts">`, import `{ usePage, Link }` from `@inertiajs/vue3`.

### F2. `resources/js/Layouts/GuestLayout.vue` — dark restyle
- Root: `flex min-h-screen flex-col items-center justify-center bg-gray-950 px-4 py-8 text-white`.
- Logo `<Link href="/">` with `ApplicationLogo` recolored (e.g. `h-16 w-16 fill-current text-indigo-500`).
- Card: `mt-6 w-full max-w-md overflow-hidden rounded-2xl border border-gray-800 bg-gray-900 px-6 py-6 shadow-xl sm:px-8`.

### F3. `resources/js/Pages/Auth/Login.vue` — dark form
- Status text: `text-green-400`.
- `InputLabel` usages: add `!text-gray-300`.
- `TextInput` usages: add `!rounded-lg !border-gray-700 !bg-gray-800 !text-white placeholder-gray-500 focus:!border-indigo-500 focus:!ring-indigo-500`.
- `Checkbox`: add `!border-gray-600 !bg-gray-800 !text-indigo-600`.
- "Remember me" text: `text-gray-400`.
- Forgot-password link: `text-indigo-400 hover:text-indigo-300`.
- `PrimaryButton`: add `!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-offset-gray-900` and keep existing `:class="{ 'opacity-25': form.processing }"`.
- No register link exists in this file; no link removal needed.

### F4. Other GuestLayout auth pages — matching dark classes
Apply the same F3 overrides so the dark layout is consistent:
- `resources/js/Pages/Auth/ForgotPassword.vue` (`text-gray-600` → `text-gray-400`, inputs/labels/button).
- `resources/js/Pages/Auth/ResetPassword.vue` (inputs/labels/button).
- `resources/js/Pages/Auth/ConfirmPassword.vue` (`text-gray-600` → `text-gray-400`, inputs/labels/button).
- `resources/js/Pages/Auth/VerifyEmail.vue` (`text-gray-600` → `text-gray-400`, green text → `text-green-400`, logout link → `text-gray-400 hover:text-white`).

### F5. `resources/js/Layouts/AuthenticatedLayout.vue` — repoint links
- Replace all 4 `route('dashboard')` / `route().current('dashboard')` with `route('control.dashboard')` / `route().current('control.dashboard')` (`:25,37,38,144,145`) and change the nav label `Dashboard` → `Control` (`:40,147`).

## Test Changes

- `tests/Feature/ExampleTest.php`: `/` now redirects for guests → assert `->assertRedirect(route('login'))` (replace the 200 assertion).
- `tests/Feature/Auth/AuthenticationTest.php:20`: expect `route('control.dashboard', absolute: false)`.
- `tests/Feature/Auth/EmailVerificationTest.php:31`: expect `route('control.dashboard', absolute: false).'?verified=1'`.
- Delete `tests/Feature/Auth/RegistrationTest.php`.
- Add a small feature test (in `ExampleTest.php` or a new `RoutingTest.php`):
  - guest `GET /control` → redirect to `/login`;
  - guest `GET /display/main` → 200;
  - authenticated `GET /login` → redirect to `/control`;
  - authenticated `GET /` → redirect to `/control`.

## Validation

1. `vendor/bin/pint --dirty`
2. `php artisan test` — full suite green (including the updated auth tests).
3. `npx vue-tsc --noEmit`
4. `npm run build`
5. Dangling-reference sweep: `rg "route\('dashboard'|RegisteredUserController|Pages/Dashboard|Pages/Welcome|Pages/Auth/Register"`.
6. Manual:
   - Guest: `/`, `/control`, `/profile`, `/dashboard` → `/login` (or 404 for `/dashboard`); `/display/main` → renders.
   - Login with `gret@admin.com` / `admin` → lands on `/control`; top bar shows name + Profile + Log Out; logout returns to `/login`.
   - Authed visit to `/login` → `/control`.
   - `/register` → 404.
   - Forgot/reset/confirm/verify screens are dark and legible.

## Risks / Notes

- Any missed `route('dashboard')` produces a 500 (route not defined) — run the sweep in Validation step 5.
- Removing the `verified` middleware with the dashboard is inert because `User` isn't `MustVerifyEmail`; verification routes remain but are effectively legacy.
- **Profile page still uses Breeze-light `AuthenticatedLayout`** and will visually clash with the new dark top bar/theme. Out of scope here; optional follow-up: convert `AuthenticatedLayout` + `Profile/Edit.vue` + the Profile partials to dark, or move Profile under `MainLayout`.
- Password-reset/forgot routes stay public by necessity (otherwise the admin cannot recover access). This is the only intentional exception beyond login + display.
- `Welcome.vue` removal drops the seeded `laravelVersion`/`phpVersion` props usage; confirm no other page imports `Welcome`.

## Out of Scope

- Restyling the Profile page / `AuthenticatedLayout` to dark.
- Converting the shared Breeze input components to a themeable/dark variant.
- Removing email-verification routes/controllers.
- Any change to the display page or public API state endpoint.
