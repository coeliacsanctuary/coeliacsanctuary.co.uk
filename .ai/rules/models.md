---
paths:
  - 'app/Models/**'
  - 'app/Models/*.php'
---

# Models

## Model events via booted(), not observers
Model event hooks and global scopes go in `protected static function booted(): void` on the model. There is no `app/Observers` directory and observers / `#[ObservedBy]` are not used.

Shared publish/live filtering uses `static::addGlobalScope(new LiveScope())` from `app/Scopes`.

## Query helpers live on a custom builder
Query helpers are public methods returning `static` on a dedicated builder in `app/Builders` (`/** @extends Builder<Model> */ class FooBuilder extends Builder`), wired to the model with `#[UseEloquentBuilder(FooBuilder::class)]`. Never write local `scope*` methods.

Type closures that receive the builder as the builder (`fn (EateryBuilder $query) => ...`) rather than `Builder` plus an inline `@var`.

## Accessors use the Attribute class
Accessors and mutators use the `Attribute` class style (`protected function foo(): Attribute { return Attribute::make(get: ...); }`).

Do not write legacy `getXxxAttribute()` / `setXxxAttribute()` magic methods.

## Models are unguarded application-wide
Never add `$fillable` or `$guarded` to a model. `Model::unguard()` is called in `AppServiceProvider` and application-wide unguarding is the deliberate choice.

## No HasFactory trait on models
Do not add the `HasFactory` trait to any model. A model should not know how to create itself in a test environment — no model in `app/Models` uses it.

Tests reach for `$this->build()` / `$this->create()` instead.
