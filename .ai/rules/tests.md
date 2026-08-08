---
paths:
  - 'tests/**'
---

# Tests

## Assert actions and pipelines with the test concerns
Assert collaborators ran with the `InteractsWithActions` / `InteractsWithPipelines` helpers rather than raw Mockery: `$this->expectAction(SomeAction::class, $args, return: ...)`, `$this->dontExpectAction(...)`, `$this->expectPipelineToRun(...)`, `$this->expectPipelineToExecute(StepClass::class)`.

Call an action directly with `$this->callAction()` and a pipeline with `$this->runPipeline()`.

## Feature tests are outside-in only, unit tests cover the class
Feature tests hit a route and assert on the response — status, validation errors, the Inertia page returned and its props. They must never exercise application code such as an Action directly; they only assert the collaborator was called (`$this->expectAction()`, `$this->mock()` etc.).

Every Action gets its own unit test covering all of its branches. The feature test does not care what the Action does.

Unit tests are free to use the database.

## Never call Model::factory() in tests
Never call `Model::factory()` anywhere in the test suite. Use `$this->build(Model::class)` to get a Factory instance back, or `$this->create(Model::class)` as the wrapper that creates it.

See `tests/Concerns/CreatesFactories.php`.

## Seed full domain models with the SeedsWebsite helpers
When a feature test needs a fully featured domain model — a blog with all its relations and media, for example — use the seeding helpers such as `$this->withBlogs()` rather than assembling the graph by hand.

See `tests/Concerns/SeedsWebsite.php` for the full list.

## Test methods use #[Test] and camelCase names
Mark test methods with the `#[Test]` attribute (and attributes for data providers) rather than a `test` prefix.

Name them in camelCase, usually with an `it` prefix — `itDoesSomething()`. Drop the prefix where it reads better, e.g. `ifDataIsMissingItReturnsAValidationError()`.
