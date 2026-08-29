---
paths:
  - 'app/Http/**'
---

# Http

## Read request input with typed getters
Read request input with the typed getters — `$request->string()`, `->integer()`, `->boolean()`, `->date()`, `->collect()`, `->enum()` — not `$request->input()`, `->get()`, or dynamic properties.

Chain `->toString()` on `string()` where a native string is needed.
