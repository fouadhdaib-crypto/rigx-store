# REGIX — PHP admin dashboard

Responsive, dark-mode PHP demo rebranded from Nexora to **REGIX** using a unified neon-lime design system.

## Launch locally

```bash
cd regix_admin
php -S localhost:8000
```

Then visit http://localhost:8000/index.php.

## Updated layout

- Removed the desktop top header. On mobile, navigation opens with the menu button next to each page heading.
- Increased sidebar width to 284px, enlarged navigation typography and improved page readability.
- Redesigned Reporting period as an accessible dark dropdown list linking to 7/30/90-day views without requiring JavaScript.

## Pages

- `index.php` — dashboard with explicitly marked illustrative demo data.
- `products.php`, `categories.php`, `users.php` — intentionally empty placeholder views ready for database integration.
- `login.php` — demo login interface; **authentication is not implemented**.
- `css/admin.css` — design tokens, layouts, interactions, mobile and reduced-motion support.
- `assets/regix-mark.svg`, `regix-logo.svg`, `favicon.svg` — scalable REGIX brand graphics.

**Important:** This archive is a visual UI demo, not a production-ready authenticated shop backend. Hook up real database data and authentication before deployment. SVG and CSS are editable without external dependencies.
