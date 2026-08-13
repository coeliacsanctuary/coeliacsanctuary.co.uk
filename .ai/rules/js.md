---
paths:
  - 'resources/js/**'
---

# Js

## FullWidthComponent is for backgrounds that bleed, not for content width
`FullWidthComponent.vue` exists so a section's *background* can bleed to the viewport edges — it was added specifically for `RecipeHeader.vue`. It is a deliberate special case, not a general layout tool.

All page content stays inside the `max-w-8xl` frame. Do not reach for `FullWidthComponent` to give a content section visual emphasis; use card styling within the normal column instead.

## Don't run prettier/eslint during a feedback loop
Run `npx prettier` / `npx eslint` once, when the work is finished and the user has said they're happy with it. Not after every follow-up tweak.

The moment the user replies with a correction or a tweak, the session is iterating — no prettier, no eslint, no pint, no phpstan, no tests until they sign off. This holds regardless of how small the edit is, which branch you're on, or whether the previous run passed. Each run costs the user a minute of waiting, and the back-and-forth is where it hurts most.

Same principle as the Pint rule in `.ai/guidelines/general-guidelines.md` — don't announce "prettier and eslint clean" after each round either.
