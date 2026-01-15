Always stick to the standards already used in the project.

Never use `$fillable` or `$guarded` properties. We call `Model::unguard()` in AppServiceProvider and prefer application-wide unguarding.

Do not use facades outside of tests, and even in tests the only acceptiple use case is to call `::fake` on the facade. Instead use dependency injection where possible, ie in the controller method arguments, or class constructor, or if that is not applicable, then use the `app()` helper to resolve an instance.

Do not add UseFactory to any models, the models should not have knowledge on how to create themselves in a test environment.

Database factories should extend the Factory class in the `database/factories` directory, rather than the vendor factory, and always ensure that the `$model` is set.

Always ensure that code passes pint (`composer pint`) and phpstan (`composer phpstan`).

Always ensure that the test suite passes (`composer test`) - ideally, only run the subset of tests needed for that feature, rather than the entire test suite.
