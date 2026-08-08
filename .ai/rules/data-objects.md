---
paths:
  - 'app/DataObjects/**'
---

# Data Objects

## DTOs are plain readonly classes
DTOs are plain `readonly class` (commonly `final readonly`) in `app/DataObjects/`, with every value as a promoted public constructor property. spatie/laravel-data is not used.

When the data needs shaping, add plain methods to the object rather than reaching for a package.
