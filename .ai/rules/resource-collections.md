---
paths:
  - 'app/ResourceCollections/**'
---

# Resource Collections

## Resource collections live in App\ResourceCollections
Resource collections live in `app/ResourceCollections/<Domain>/`, named `*Collection`, extending `ResourceCollection` and declaring `public $collects = SomeResource::class;`.
