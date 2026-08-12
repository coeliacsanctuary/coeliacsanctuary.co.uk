---
paths:
  - 'resources/js/**'
---

# Js

## FullWidthComponent is for backgrounds that bleed, not for content width
`FullWidthComponent.vue` exists so a section's *background* can bleed to the viewport edges — it was added specifically for `RecipeHeader.vue`. It is a deliberate special case, not a general layout tool.

All page content stays inside the `max-w-8xl` frame. Do not reach for `FullWidthComponent` to give a content section visual emphasis; use card styling within the normal column instead.
