---
paths:
  - 'app/Resources/**'
---

# Resources

## JSON Resources live in App\Resources
JSON resources live in `app/Resources/<Domain>/` under the `App\Resources` namespace, not `app/Http/Resources`.

Each is named `*Resource`, extends `JsonResource`, carries a `/** @mixin Model */` docblock for the model it wraps, and types `toArray()` with an array-shape `@return`. Never use the `request()` helper inside a resource.
