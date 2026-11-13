# SSG MART

A Laravel 12 grocery store for SSG MART, built with PHP 8.2+, Laravel Breeze, Blade, Tailwind CSS, Alpine.js, and MySQL. The storefront, products, promotional content, checkout, customer accounts, and admin permissions are database-driven.

## Requirements

- PHP 8.2 or newer with `bcmath`, `ctype`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, and `tokenizer`
- Composer 2
- Node.js 20+ and npm
- MySQL 8+ (SQLite is also supported for local development and tests)

## Local setup

1. Create a MySQL database named `ssg_mart`, then copy `.env.example` to `.env`.
2. Set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for your MySQL installation. Set a unique `APP_KEY` with `php artisan key:generate`.
3. Before the first seed, set `SSG_SUPER_ADMIN_EMAIL` and a strong `SSG_SUPER_ADMIN_PASSWORD` in `.env`.
4. Install and initialize the application:

   ```sh
   composer install
   npm install
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

5. Start the app with `php artisan serve`.

The seed creates the initial grocery catalogue, homepage content, legal/help pages, the Super Admin role, and a Super Admin account. The development fallback credentials are `admin@ssgmart.local` / `ChangeMe@123!`; override both before seeding and never use the fallback password in a deployed environment.

The sample catalogue focuses on familiar Indian household essentials and Uttar Pradesh favourites, including Muzaffarnagar jaggery. The catalogue refresh archives legacy items instead of deleting product records referenced by old orders; order-item name and price snapshots remain intact. Seeded product artwork is generated locally when an admin has not uploaded a product image.

## Store management

- **Storefront:** `/`, `/shop`, `/products/{slug}`, `/cart`, `/checkout`, and `/contact`
- **Customer account:** `/register`, `/login`, and `/account` for profile details, addresses, wishlist, order history, and printable order invoices
- **Admin panel:** `/admin` for catalogue, inventory, categories, brands, orders, coupon offers, banners, testimonials, published pages, customer enquiries, delivery fees, website details, SEO metadata, and activity history
- **RBAC:** the Super Admin manages Admin accounts and grants per-module `view`, `create`, `edit`, and `delete` permissions. Admin accounts cannot access Super Admin account-management routes or change roles.
- **Store configuration:** Super Admins can update public business/contact information, homepage copy, SEO metadata, delivery fees, and free-delivery thresholds in the panel. Google Maps, click-to-call, and WhatsApp links use those saved values.

Laravel Breeze provides registration, login/logout, password reset, and signed email-verification flows. Configure a production mail transport before relying on verification and password-reset emails.

Online checkout orders are recorded as payment-pending; cash on delivery is available immediately. No payment processor or payment credentials are included. Keep credentials in environment variables and connect a verified gateway adapter before accepting real online payments. Do not mark an online order paid until the provider's signed callback has been verified.

## Tests and builds

```sh
php artisan test
npm run build
```

In production, use HTTPS, set `APP_DEBUG=false`, configure mail and database credentials, replace the seeded Super Admin password, and run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` as part of deployment. For Apache hosting, point the domain document root to `public/` when possible. If the project root must remain the document root, deploy the root `index.php` and `.htaccess` files; they forward application requests to Laravel and keep private project files from being served directly.
