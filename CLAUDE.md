# ClientManager

Laravel 12 + Inertia 2 + Vue 3 (Vite, Tailwind). Local database is MySQL under MAMP; tests use in-memory SQLite.

## Read this first

Before working on a module, read its doc instead of scanning the codebase. Open source files only as the doc points you to them.

| Module | Doc |
| --- | --- |
| Bills (`/bills`) | [docs/BILLS_MODULE.md](docs/BILLS_MODULE.md) |
| Domains, hosting, SSL | [docs/SERVICES_MODULE.md](docs/SERVICES_MODULE.md) |

Other guides: [CURRENCY_CONFIG.md](CURRENCY_CONFIG.md), [DATABASE_SWITCHING_GUIDE.md](DATABASE_SWITCHING_GUIDE.md), [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md), [NGINX_DEPLOYMENT_GUIDE.md](NGINX_DEPLOYMENT_GUIDE.md).

When you change a module, update its doc in the same change. When you learn a module well enough to save the next person a search, add a doc and list it here.

## Roles and access

- **Roles:** `users.user_type` is `account_manager` or `approver`. `User::canApproveBills()` means an active approver.
- **Inactive users:** users with `is_active = false` cannot log in. The `active` middleware on the main route group also signs them out mid-session.
- **Approver-only:** deleting clients, domains, hosting, SSL and bills. Pages hide these buttons using the shared Inertia prop `auth.canApprove`, and controllers return 403.
- **Open to every user:** Employees, Payroll and Settings have no role checks. This was a deliberate decision.

## Commands

```bash
php artisan test --filter=BillCreationTest
php artisan test
php artisan migrate
npm run build
```

`php artisan migrate` needs MAMP's MySQL running. Tests do not.

## Layout

- `routes/web.php`: all routes
- `app/Http/Controllers/`: one controller per module; validation lives in the controller
- `resources/js/Pages/<Module>/`: Inertia pages (`Index`, `Create`, `Edit`, `Show`)
- `resources/js/Components/`: shared inputs (`TextInput`, `Checkbox`, `InputLabel`, `InputError`)
- `resources/js/utils/`: `currency.js`, `servicePeriod.js`

## Users

There is no public registration. Account managers and approvers create users from User Management (`/user-management`). Do not add a `/register` route.

## Tests

All tests should pass.
