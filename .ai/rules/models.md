---
paths:
  - 'app/Models/**'
  - 'app/Models/*.php'
---

# Models

## Model events via booted(), not observers
Model event hooks and global scopes go in `protected static function booted(): void` on the model. There is no `app/Observers` directory and observers / `#[ObservedBy]` are not used.

Shared publish/live filtering uses `static::addGlobalScope(new LiveScope())` from `app/Scopes`. Query helpers are local `public function scope*(Builder $query): Builder` methods on the model.

## Accessors use the Attribute class
Accessors and mutators use the `Attribute` class style (`protected function foo(): Attribute { return Attribute::make(get: ...); }`).

Do not write legacy `getXxxAttribute()` / `setXxxAttribute()` magic methods.

## Models are unguarded application-wide
Never add `$fillable` or `$guarded` to a model. `Model::unguard()` is called in `AppServiceProvider` and application-wide unguarding is the deliberate choice.

## No HasFactory trait on models
Do not add the `HasFactory` trait to any model. A model should not know how to create itself in a test environment — no model in `app/Models` uses it.

Tests reach for `$this->build()` / `$this->create()` instead.
