---
paths:
  - 'app/Enums/**'
---

# Enums

## Enums expose a name() label method
Enums expose their human-readable label via a `public function name(): string` method using `match ($this)`.

This intentionally shadows PHP's built-in `$case->name` property — do not read the raw case name for display, and do not add a separate `label()` or `title()` method.
