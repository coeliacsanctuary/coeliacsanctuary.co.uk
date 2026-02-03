It is acceptable for unit tests to use the database.

Feature tests should be used only for testing 'outside in' functionality, for example hitting a route and asserting on the response.

Feature tests should never test application code (For example an action) directly, more ensure that the action was called using `$this->mock()` etc.

Feature tests assert against responses, validation rules, that the correct inertia page was returned and the correct data, etc.

Unit tests should be used for testing individual classes and methods, for example each action has a unit test covering all parts of that action, as stated before the feature test ensures the action was called, it doesnt care what it does, the action has its own unit test.

Never call Model::factory() anywhere in testing, instead reach for `$this->build(Model::class)` that returns a Factory instance, or `$this->create(Model::class)` which is just a wrapper around creating the factory. See tests/Concerns/CreatesFactories.php for details.

When feature testing a route that requires a fully featured domain model, for example a blog, with all relations and media, then reach for `$this->withBlogs()` method, for full details and available methods see tests/Concerns/SeedsWebsite.php

Stick to the existing conventions in the app for testing.

Use the `#[Test]` attribute on your test classes, and any attributes for data providers.

Write test names in camelCase, ie `itDoesSomething` and in most cases use the it prefix, but it can be ommited where it makes sense, eg `ifDataIsMissingItReturnsAValidationError()`
