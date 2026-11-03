# ClientManager

Laravel 12 + Inertia 2 + Vue 3 (Vite, Tailwind). Local database is MySQL under MAMP; tests use in-memory SQLite.

## Read this first

Before working on a module, read its doc instead of scanning the codebase. Open source files only as the doc points you to them.

| Module | Doc |
| --- | --- |
| Bills (`/bills`) | [docs/BILLS_MODULE.md](docs/BILLS_MODULE.md) |

Other guides: [CURRENCY_CONFIG.md](CURRENCY_CONFIG.md), [DATABASE_SWITCHING_GUIDE.md](DATABASE_SWITCHING_GUIDE.md), [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md), [NGINX_DEPLOYMENT_GUIDE.md](NGINX_DEPLOYMENT_GUIDE.md).

When you change a module, update its doc in the same change. When you learn a module well enough to save the next person a search, add a doc and list it here.

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

## Known failing tests

`Tests\Feature\Auth\RegistrationTest` has 2 failing tests because `/register` returns 404. Everything else should pass.
