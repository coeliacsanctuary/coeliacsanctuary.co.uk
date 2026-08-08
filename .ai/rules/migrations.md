---
paths:
  - 'database/migrations/**'
---

# Migrations

## One-way migrations, no foreign key constraints
Migrations implement `up()` only — never write a `down()` method.

Foreign keys are plain integer columns; do not add `->constrained()`, `->foreign()`, or `foreignIdFor()` constraints.
