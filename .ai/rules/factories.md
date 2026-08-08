---
paths:
  - 'database/factories/**'
---

# Factories

## Factories extend the local Factory base class
Factories extend `Database\Factories\Factory` (the abstract class in `database/factories/Factory.php`), never `Illuminate\Database\Eloquent\Factories\Factory` directly — the local base overrides `resolveFactoryName()` to flatten namespaced models onto `<Model>Factory`.

Always set the `$model` property explicitly.
