Do not, under any circumstances, run migrations.

Always ensure that code passes pint (`composer pint`) and phpstan (`composer phpstan`).

Always ensure that the test suite passes (`composer test`) - ideally, only run the subset of tests needed for that feature, rather than the entire test suite.
