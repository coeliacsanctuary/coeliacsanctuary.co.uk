---
paths:
  - 'app/Actions/**'
---

# Actions

## Action classes: handle() with an Action suffix
Business logic lives in Action classes under `app/Actions/<Domain>/`, named with an `Action` suffix and exposing a single public `handle()` method.

Inject the action into a controller's `__invoke()` signature and call `->handle()`. Do not put the logic in the controller.
