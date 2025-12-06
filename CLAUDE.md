# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Perfex CRM** based ERP application with custom modules for HR Payroll, Document Management, Warehouse Management, and other business operations. Built on **CodeIgniter 3** framework with Vue.js 3 components and TailwindCSS styling.

**Project URL:** https://erp.rwad.org/

**Key Technologies:**
- PHP 8.1+ (CodeIgniter 3 framework)
- MySQL database
- Vue.js 3 for reactive components
- Laravel Mix for asset compilation
- TailwindCSS with `tw-` prefix
- Bootstrap 3.4.1 (legacy UI)

## Development Commands

### Frontend Build & Watch
```bash
# Install dependencies
npm install

# Development build
npm run dev

# Watch for changes (development)
npm run watch

# Production build
npm run prod

# Full production build with versioning
npm run build
```

### Backend/PHP
```bash
# Install PHP dependencies
composer install

# Update dependencies
composer update
```

### Running the Application
This is a PHP application that requires a web server (Apache/IIS). The `.htaccess` file handles URL rewriting and redirects root to `/admin`.

**Important:** Environment is set to `production` in `index.php:60`. Change to `development` for debugging.

## Architecture Overview

### CodeIgniter MVC Structure

**Application Entry Point:**
- `index.php` - Front controller, loads CodeIgniter from `system/` directory
- `application/` - Main application code
- `modules/` - HMVC-style modules for extended functionality

**Core Components:**
- **Controllers:** `application/controllers/` - Handle HTTP requests
- **Models:** `application/models/` - Database interaction layer
- **Views:** `application/views/` - Template files (PHP-based)
- **Libraries:** `application/libraries/` - Custom business logic classes
- **Helpers:** `application/helpers/` - Utility functions (58+ helper files)

### Module System

This application uses an **HMVC modular architecture**. Modules are located in `modules/` directory:

**Key Modules:**
- `hr_payroll` - HR and payroll management
- `document_management` - Document workflow system
- `warehouse` - Inventory management
- `manufacturing` - Production management
- `purchase` - Procurement system
- `accounting` - Financial accounting
- `surveys` - Survey/feedback system
- `backup` - Database backup functionality
- `equipments` - Equipment tracking
- `documentworkflow` & `dw_template` - Document approval workflows

Each module follows CodeIgniter structure with its own:
- `controllers/` - Module-specific controllers
- `models/` - Module-specific models
- `views/` - Module-specific views
- `libraries/` - Module-specific libraries
- `helpers/` - Module-specific helpers
- `language/` - Localization files
- `assets/` - Module-specific assets

### Configuration

**Critical Config Files:**
- `application/config/app-config.php` - Main app configuration (DB, base URL)
- `application/config/config.php` - CodeIgniter configuration
- `application/config/database.php` - Database connection settings
- `application/config/routes.php` - URL routing rules
- `application/config/autoload.php` - Auto-loaded libraries and helpers
- `application/config/migration.php` - Database migration version tracking

**Database Prefix:**
The `db_prefix()` function (defined in `config.php`) returns table prefix - default is `tbl`.

### Hook System

The application uses a custom **action/filter hook system** similar to WordPress:
- `application/third_party/action_hooks.php` - Legacy hook implementation
- Newer code uses `hooks()->do_action()` and `hooks()->add_action()`
- Modules can hook into core functionality without modifying core files

### Asset Pipeline

**Laravel Mix Configuration** (`webpack.mix.js`):
- Compiles Vue.js components from `resources/js/app.js` to `assets/builds/`
- Processes TailwindCSS from `resources/css/tailwind.css` to `assets/builds/`
- Minifies JavaScript files in `assets/js/` and `assets/themes/perfex/js/`
- Combines vendor libraries into bundles:
  - `vendor-admin.js` - jQuery, Bootstrap, DataTables, etc.
  - `common.js` - Internal plugins and utilities
  - `vendor-admin.css` - Combined vendor stylesheets
- Production builds inject version numbers from `migration.php`

**TailwindCSS Configuration** (`tailwind.config.js`):
- Prefix: `tw-` (to avoid Bootstrap conflicts)
- Content paths: `application/views/**`, `modules/**/views/**`, `resources/js/**`
- Preflight disabled to maintain Bootstrap compatibility
- Safelist patterns for dynamic classes

**Important:** Always use `tw-` prefix for Tailwind utility classes to avoid conflicts with Bootstrap.

### Key Application Libraries

**Core Libraries** (`application/libraries/`):
- `App.php` - Main application logic and utilities
- `App_modules.php` - Module management system
- `App_mailer.php` - Email handling
- `App_menu.php` - Admin menu builder
- `App_items_table.php` - Invoice/estimate line items
- `App_Migration.php` - Database migration handler
- `App_pusher.php` - Real-time notifications (Pusher integration)
- Payment gateway integrations: Stripe, Braintree, PayPal, etc.

### Database & Migrations

**Migration System:**
- Migrations located in `application/migrations/` and `modules/*/migrations/`
- Current version tracked in `application/config/migration.php`
- Module-specific migrations handled by `App_module_migration` library
- Migration format: `XXX_version_Y_Y_Y.php` (sequential number + semantic version)

### Views & Templating

**View Structure:**
- Admin area: `application/views/admin/`
- Client area: `application/views/themes/perfex/`
- Authentication: `application/views/authentication/`
- Shared includes: `application/views/admin/includes/`

**Template Rendering:**
- Uses PHP-based templates (not Blade)
- Helper functions for rendering: `admin_head()`, `admin_header()`, `admin_footer()`
- Module views loaded via CodeIgniter's HMVC view loader

### Authentication & Permissions

**Models:**
- `Authentication_model` - Login/logout, session management
- `Staff_model` - Staff user management
- `Clients_model` - Client user management
- `Roles_model` - Permission system

**Permission Checks:**
- `has_permission()` helper function
- Staff permissions controlled via roles
- Client area has separate permission system

## Common Development Patterns

### Adding a New Module Feature

1. Create module directory in `modules/your_module/`
2. Add module registration in module's main PHP file
3. Create controllers, models, views following CodeIgniter conventions
4. Use hooks to integrate with core application
5. Add menu items via `App_menu` library or hooks
6. Create migrations for database changes

### Working with Models

Models extend `App_Model` (or `CI_Model`). Common patterns:
```php
$this->load->model('your_model');
$data = $this->your_model->get($id);
```

Use `db_prefix()` for table names:
```php
$this->db->where('id', $id)->get(db_prefix() . 'table_name');
```

### Using Hooks

**Add action:**
```php
hooks()->add_action('action_name', 'callback_function', priority);
```

**Trigger action:**
```php
hooks()->do_action('action_name', $args);
```

**Add filter:**
```php
hooks()->add_filter('filter_name', 'callback_function', priority);
```

**Apply filter:**
```php
$value = hooks()->apply_filters('filter_name', $value, $args);
```

### Helper Functions

The application has 58+ helper files. Key helpers:
- `general_helper.php` - Core utilities
- `func_helper.php` - Common functions
- `admin_helper.php` - Admin area utilities
- `clients_helper.php` - Client area utilities
- `sales_helper.php` - Invoice/estimate/proposal helpers
- `misc_helper.php` - Miscellaneous utilities

Load helpers in controllers:
```php
$this->load->helper('helper_name');
```

### Working with Assets

**After modifying JS/CSS:**
```bash
npm run dev  # Development
npm run prod # Production (minified + versioned)
```

**Key asset files:**
- `assets/js/main.js` - Core application JS
- `assets/css/style.css` - Main stylesheet
- `assets/themes/perfex/css/style.css` - Theme styles
- Module assets: `modules/module_name/assets/`

## Testing & Debugging

**Enable Debug Mode:**
Change environment in `index.php:60`:
```php
define('ENVIRONMENT', 'development');
```

**Database Queries:**
Enable profiler in controllers:
```php
$this->output->enable_profiler(TRUE);
```

**Error Logs:**
- CodeIgniter logs: `application/logs/`
- PHP error logs: Check web server configuration

## Important Notes

- **Never commit** `application/config/app-config.php` - contains credentials
- **Database prefix** is configurable via `db_prefix()` function
- **TailwindCSS classes** must use `tw-` prefix due to Bootstrap conflicts
- **Module hooks** are the preferred way to extend core functionality
- **Migration version** in `migration.php` is used for asset cache busting
- The application auto-redirects root URL to `/admin` via `.htaccess`
- Minimum PHP version: 8.1 (enforced in `config.php`)
