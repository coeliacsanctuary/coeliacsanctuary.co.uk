---
paths:
  - 'app/**'
---

# App

## New classes must satisfy the architecture test
Every `app/` subdirectory has an enforced naming and shape contract asserted in `tests/Code/CodeArchitectureTest.php` — suffix, base class or interface, and required method.

Before adding a class to a directory, read the matching assertion there and match it. Add a new assertion when introducing a new directory. `app/Concerns` and `app/Ai/Concerns` hold only traits; `app/Contracts` holds only interfaces.

## Facades: DI first, with a known exception set
Prefer dependency injection — constructor, or the `__invoke()` / `handle()` signature — falling back to the `app()` helper where injection isn't possible.

`DB`, `Cache` and `URL` are established exceptions and fine to use directly. Other facades are case by case: check sibling files in the same directory before reaching for one.

Never use the `Request`, `Auth` or `View` facades. Type-hint `Request` in the method signature instead, and never use the `request()` helper.
