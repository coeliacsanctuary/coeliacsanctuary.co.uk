---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Single-action invokable controllers
Controllers are single-action and invokable: one `public function __invoke()`, a `Controller` suffix, and no base controller class to extend (none exists).

Name by HTTP verb intent — `IndexController`, `ShowController`, `StoreController`, `GetController`, `UpdateController`, `DestroyController`, `CreateController` — and namespace by URL segment, e.g. `App\Http\Controllers\EatingOut\County\Town\ShowController`.

Never use the `request()` helper; type-hint `Request` or a Form Request in `__invoke()`. Inject Actions and Pipelines into the `__invoke()` signature rather than building logic inline.

## Return Inertia views through the response builder
Never call the `Inertia` facade or `inertia()` helper to return a view. Use the response builder at `app/Http/Response/Inertia.php` — every controller that renders a page goes through it.
