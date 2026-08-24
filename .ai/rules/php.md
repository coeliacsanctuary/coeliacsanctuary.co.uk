---
paths:
  - '**/*.php'
---

# PHP

## No custom class constants, protected over private
Never create or use custom class constants unless absolutely necessary — there is almost always a better option. This applies to using existing custom constants as well as declaring new ones. Framework and vendor constants are fine.

In order of preference: inline the literal where it's used (usually the answer); a class property; `config/` when it is genuine environment-varying configuration; an enum in `app/Enums/` when it is a named set of values (see `OrderState`). A `const` only when none of those fit, and expect to justify it.

Don't compensate with an explanatory comment either — the code carries it.

Default to `protected` for methods and properties; only use `private` where there is a good reason.
