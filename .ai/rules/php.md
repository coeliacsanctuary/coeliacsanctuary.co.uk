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

## Don't run pint, stan, rector etc during a feedback loop
Run `composer rector`, `composer pint`, `composer stan` etc once, when the work is finished and the user has said they're happy with it. Not after every follow-up tweak, and only when sticking to the previously agreed guidelines in general-guidelines.md

The moment the user replies with a correction or a tweak, the session is iterating — no rector, no pint, no phpstan, no tests until they sign off. This holds regardless of how small the edit is, which branch you're on, or whether the previous run passed. Each run costs the user a minute of waiting, and the back-and-forth is where it hurts most.

Same principle as the Pint rule in `.ai/guidelines/general-guidelines.md` — don't announce "prettier and eslint clean" after each round either.
