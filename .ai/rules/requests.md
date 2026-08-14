---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Form Requests only, exposing typed helper methods
All validation goes in a Form Request under `app/Http/Requests/<Domain>/`, with a `Request` suffix, extending `FormRequest` and defining `rules()` with array-syntax rules. Never use `$request->validate()` or `Validator::make()`.

Add helper methods to the request to hand typed data to the controller, e.g. `toContactDto()`, `resolveItem()`. Never use the `request()` helper inside a request class.
