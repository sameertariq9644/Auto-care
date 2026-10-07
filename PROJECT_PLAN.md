# AutoCare Pro – Implementation Plan

## Project Overview
AutoCare Pro is a web-based SaaS platform that lets vehicle owners book services online while enabling workshops to manage mechanics, inventory, invoices, customers, and service history. Every vehicle gets a unique QR code that, when scanned, instantly shows its service history.

## Detected Stack (already scaffolded in this repo)
- Laravel 13 (PHP 8.3)
- Laravel Breeze (authentication scaffolding)
- Spatie Laravel Permission (roles & permissions)
- endroid/qr-code (QR code generation)
- barryvdh/laravel-dompdf (PDF invoices)
- Tailwind CSS + Vite (frontend)

Only the default Breeze scaffold exists so far (users table, User model, `/dashboard`, `/profile` routes). Everything below is new work.

---

## Phase 1 — Foundations

### 1.1 Define roles
Three roles via Spatie Permission:
- **Admin / Workshop Owner** — manages workshop, mechanics, inventory, invoices, reports.
- **Mechanic** — views assigned jobs, updates job/service status.
- **Customer** — registers vehicles, books services, views service history via QR code or account.

Steps:
1. Publish Spatie config: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`.
2. Run its migration.
3. Add `HasRoles` trait to `User` model.
4. Create a seeder that creates the three roles (`admin`, `mechanic`, `customer`) and an initial admin user.

### 1.2 Database design (core tables)
- `users` (exists) — add `phone`, `role` context via Spatie roles.
- `workshops` — if supporting multiple workshops (optional for v1; can start single-tenant).
- `vehicles` — `id`, `user_id` (owner), `make`, `model`, `year`, `plate_number`, `vin`, `qr_code_path`/`qr_uuid`.
- `mechanics` — `id`, `user_id`, `specialty`, `status` (or just extend `users` with a mechanic profile).
- `service_bookings` — `id`, `vehicle_id`, `customer_id`, `requested_service`, `preferred_date`, `status` (pending/confirmed/in_progress/completed/cancelled), `mechanic_id` (nullable, assigned later).
- `service_records` (service history) — `id`, `vehicle_id`, `booking_id`, `mechanic_id`, `description`, `parts_used`, `cost`, `serviced_at`.
- `inventory_items` — `id`, `name`, `sku`, `quantity`, `unit_price`, `reorder_level`.
- `service_record_items` (pivot) — links `service_records` to `inventory_items` with `quantity_used`.
- `invoices` — `id`, `booking_id`, `customer_id`, `subtotal`, `tax`, `total`, `status` (unpaid/paid), `pdf_path`.
- `invoice_items` — `id`, `invoice_id`, `description`, `quantity`, `unit_price`.

Steps:
1. Create migrations for each table above (`php artisan make:model X -mfc` to get model, migration, factory, controller together).
2. Define relationships in Eloquent models (`User hasMany Vehicles`, `Vehicle hasMany ServiceRecords`, `Booking belongsTo Vehicle/Mechanic`, `Invoice hasMany InvoiceItems`, etc.).
3. Run `php artisan migrate`.

---

## Phase 2 — Auth & Access Control
1. Install/verify Breeze views are published (`php artisan breeze:install blade` if not already run).
2. After registration, default new users to the `customer` role.
3. Build an admin-only "Create Mechanic" flow (admin creates mechanic accounts, assigns `mechanic` role).
4. Add middleware/gates so:
   - Customers only see their own vehicles/bookings.
   - Mechanics only see jobs assigned to them.
   - Admin sees everything.
5. Redirect users to role-specific dashboards after login (customer dashboard vs. admin dashboard vs. mechanic dashboard).

---

## Phase 3 — Vehicle Management & QR Codes
1. Customer-facing CRUD for vehicles (`VehicleController`): add/edit/delete vehicle, list "My Vehicles".
2. On vehicle creation, generate a unique identifier (UUID) and a QR code image using `endroid/qr-code` that encodes a public URL like `/vehicles/{uuid}/history`.
3. Store the QR image (SVG/PNG) in `storage/app/public/qrcodes` and save the path on the vehicle record.
4. Build a **public** (or auth-gated, decide per privacy needs) route `/vehicles/{uuid}/history` that shows read-only service history — this is what scanning the QR code opens.
5. Display/download the QR code on the vehicle's detail page (customer can print and stick it on the car).

---

## Phase 4 — Booking System
1. Customer flow: select a vehicle → choose service type → pick preferred date/time → submit booking (status `pending`).
2. Admin flow: view incoming bookings, confirm them, assign a mechanic, set status to `confirmed`/`in_progress`.
3. Mechanic flow: view assigned bookings, update status as work progresses, mark `completed`.
4. Add booking status history/timeline for transparency (customer can track progress).
5. (Optional) Email/notification to customer on status changes using Laravel Notifications.

---

## Phase 5 — Service History & Inventory
1. When a mechanic completes a booking, they create a `service_record`: description of work, parts/inventory used, labor cost.
2. Selecting inventory items on the service record automatically decrements `inventory_items.quantity`.
3. Build an Admin inventory module: CRUD for parts, stock levels, low-stock alerts (based on `reorder_level`).
4. Service history per vehicle accumulates over time and is what's shown at the QR-code URL and in the customer dashboard.

---

## Phase 6 — Invoicing
1. When a service record is completed, auto-generate an invoice (subtotal from labor + parts used, plus tax).
2. Admin can review/edit the invoice before finalizing.
3. Use `barryvdh/laravel-dompdf` to render a PDF invoice from a Blade view; store it or generate on-demand for download.
4. Customer can view/download their invoices from their dashboard.
5. (Optional) Track payment status (`unpaid`/`paid`) and add a "mark as paid" action for admin.

---

## Phase 7 — Dashboards & Reporting
1. **Customer dashboard**: vehicles, upcoming bookings, recent service history, invoices.
2. **Mechanic dashboard**: assigned jobs today, job queue, quick status updates.
3. **Admin dashboard**: booking overview, revenue summary (from invoices), low-stock inventory warnings, mechanic workload.
4. Use simple Blade + Tailwind cards/tables first; add charts later if needed (e.g., Chart.js) for revenue/service trends.

---

## Phase 8 — Polish & UX
1. Consistent Tailwind layout: sidebar/topbar per role, responsive design.
2. Form validation (Laravel Form Requests) for all create/update actions.
3. Empty states, flash messages, confirmation modals for destructive actions.
4. File/QR image handling: ensure `storage:link` is run (`php artisan storage:link`) so QR codes and PDFs are publicly accessible.

---

## Phase 9 — Testing
1. Feature tests per role: registration/login, vehicle CRUD, booking lifecycle, invoice generation, QR history page access.
2. Unit tests for cost calculation logic (invoice totals, inventory deduction).
3. Run `php artisan test` (Pest/PHPUnit already in `composer.json`) before each milestone.

---

## Phase 10 — Deployment
1. Choose hosting (e.g., a VPS, Laravel Forge, or a platform like Render/Railway) and a production database (MySQL/Postgres instead of SQLite).
2. Set environment variables (`.env`) for mail, database, app URL.
3. Run `composer install --no-dev`, `npm run build`, `php artisan migrate --force`, `php artisan storage:link`.
4. Set up HTTPS (needed for QR codes to be reliably scanned from real devices) and a proper domain.
5. Configure queue worker (already scaffolded via `laravel/pail` + queue in `composer.json`'s `dev` script) for notification/PDF jobs in production (e.g., Supervisor).

---

## Suggested Build Order (Milestones)
1. Roles & auth (Phase 1–2)
2. Vehicles + QR codes (Phase 3)
3. Bookings (Phase 4)
4. Service history + inventory (Phase 5)
5. Invoicing/PDF (Phase 6)
6. Dashboards (Phase 7)
7. Polish, tests, deploy (Phase 8–10)

This order lets you demo core value (book → service → QR history) early, then layer inventory/invoicing/reporting on top.
