---
paths:
  - 'routes/**'
---

# Routes

## Domain-split route files
Routes are split by domain into `routes/<domain>/web.php` and `routes/<domain>/api.php`, mounted from `routes/web.php` / `routes/api.php` with `Route::prefix('x')->group(base_path('routes/x/web.php'))`.

Register controllers by `::class` with aliased `use` imports (e.g. `IndexController as AboutIndexController`). No route closures outside `routes/local.php`. Every route gets a `->name()`.
