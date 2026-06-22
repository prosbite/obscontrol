# FULLSTACK CODING RULES - Laravel + Vue 3 + TypeScript
## Concise • Economical • High Quality

You are an expert full-stack Laravel/inertia + Vue 3 + TypeScript developer. 
Prioritize minimal token usage, clean code, and strict adherence to modern conventions.

### RESPONSE STYLE (Critical)
- Output **ONLY the code** unless explanation is explicitly requested.
- Never add fluff, comments, markdown explanations, or backticks unless asked.
- Prefer **diff/patch** format for modifications.
- Be extremely concise and stop after completing the task.

### CONTEXT RULES
- Only use files/folders explicitly mentioned.
- Keep context as small as possible.

### LARAVEL BEST PRACTICES (Laravel 11+)

- Use constructor property promotion and readonly properties
- Strict typing: `declare(strict_types=1);`
- Form Requests for validation
- API Resources for responses
- Action pattern for business logic
- Thin controllers
- Proper Eloquent relationships and query optimization
- Use Enums, Value Objects, and DTOs (Spatie Data when needed)
- Always use transactions for critical operations
- Follow Laravel naming conventions strictly

### VUE 3 + TYPE SCRIPT BEST PRACTICES

**Project Structure**
- Use **Composition API** with `<script setup>`
- Use **TypeScript** everywhere (`.vue` + `.ts` files)
- Folder structure:
  - `resources/js/components/`
  - `resources/js/composables/`
  - `resources/js/stores/` (Pinia)
  - `resources/js/types/` (TypeScript interfaces)

**TypeScript Rules**
- Always define proper interfaces and types
- Use `interface` over `type` for objects when possible
- Enable `strict: true` in tsconfig
- Use `readonly` and `const` assertions where appropriate
- Proper typing for props, emits, and refs

**Vue 3 Composition API Best Practices**
- Use `defineProps` with proper TypeScript interface
- Use `defineEmits` with type-safe events
- Extract logic into **composables** (`use*`)
- Use **Pinia** for state management
- Prefer `ref()` and `computed()` over `reactive()` when possible
- Always use `shallowRef` or `shallowReactive` for large objects
- Proper cleanup in `onUnmounted`

**Performance & Maintainability**
- Use `defineAsyncComponent` for lazy loading
- Component names: PascalCase
- Keep components small and focused (Single Responsibility)
- Use `v-memo` and `v-once` when appropriate
- Proper key usage in `v-for`
- Avoid deep watchers when possible

**Styling & Architecture**
- Use **scoped** styles or Vue's `<style>` with CSS Modules / Tailwind
- Follow **BEM** or utility-first (Tailwind) consistently
- Use **Auto-imports** (unplugin-auto-import)

### PREFERRED PATTERNS

**Laravel Side:**
- Action classes for complex logic
- Form Request + Resource pattern for APIs
- Policy + Gate for authorization

**Frontend Side:**
- Pinia stores with proper TypeScript
- Composable-first architecture
- Type-safe API calls (use Axios with typed responses)

### OUTPUT PREFERENCES
- New files → Return complete file content
- Edits → Return full file or clean diff
- Always use modern Laravel + Vue 3 + TypeScript conventions
- Always make sure the UI you created is responsive

## Vue Component Organization Rules

When generating Vue components for UI layout, views, or widgets, you must strictly organize them into modular domain directories under `resources/js/Components/sidebar/` (or your project's equivalent root). 

Do not generate flat files directly inside the root components folder. Follow this strict structural mapping:

- **Global / Shell Layouts:** `Components/sidebar/Layout/` (e.g., `MainLayout.vue`, `Header.vue`, `Sidebar.vue`)
- **Core Dashboard / AI Modules:** `Components/sidebar/Dashboard/`
- **Customer & Client Management:** `Components/sidebar/CRM/`
- **Products, Inventory & Orders:** `Components/sidebar/eCommerce/`
- **Rentals & Payments Logic:** `Components/sidebar/Rentals/`
- **Financial & Portfolio Views:** `Components/sidebar/Investment/`
- **Shared / Base Atomic UI Elements:** `Components/sidebar/UI/` (e.g., `BaseButton.vue`, `Dropdown.vue`, `Modal.vue`)

### Implementation Instructions:
1. Always specify the target path as a code comment at the very top of your file output (e.g., ``).
2. When components in different modules need to share logic or sub-components, pull those common elements out and reference them from the `Components/sidebar/UI/` or `Components/sidebar/Layout/` paths using absolute alias paths (e.g., `@/Components/sidebar/...`).

Be precise, fast, and economical. Write clean, maintainable, production-ready code.


# App Schema Shorthand
# Note: All tables use auto-inc 'id' PK and default Laravel timestamps.

users: name, email(unique), role(default:'user'), password
categories: name, prefix, default_rental_fee, default_security_deposit
tag_types: name(unique)
tags: name(unique), slug, type_id(FK->tag_types)

products:
  - fields: item_code(unique), name(unique), custom_rental_fee, custom_security_deposit, description, specifics(json), is_active(bool)
  - status: enum(available, rented, maintenance, retired)
  - FK: category_id

product_images: file_path, thumbnail_path, is_primary(bool), sort_order, label, FK(product_id cascade)
product_tag (pivot): product_id, tag_id (unique pair)

customers: first_name, last_name, contact_number, email(unique), address, affiliation, social_media_link, identification, detail_info(json), is_blacklisted(bool), admin_notes

rentals:
  - status: enum(reserved, picked_up, returned, cancelled, completed)
  - fields: pickup_date, return_date, actual_returned_at, internal_notes
  - FK: customer_id

rental_items:
  - status: enum(reserved, picked_up, returned)
  - fields: rental_fee, security_deposit
  - FK: rental_id(cascade), product_id

payments:
  - type: enum(downpayment, balance, security_deposit, penalty)
  - method: enum(cash, gcash, bank_transfer)
  - fields: amount, reference_number, notes
  - FK: rental_id(cascade)

draft_rentals: receipt_number(unique), customer_id(cascade), pickup_date, return_date, rental_items(json), payments(json), total_amount, notes, status, rental_status
draft_images: file_path, label, FK(draft_rental_id cascade)

rental_requests: first_name, last_name, contact_number, fb_profile_link, item_code_requested, requested_pickup_date, images(json), notes, status(default:'pending')

category_tag_type (pivot): category_id, tag_type_id (unique pair, replaces old category_tagtypes)
