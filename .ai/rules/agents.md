---
paths:
  - 'app/Ai/Agents/**'
---

# Agents

## Structured output schemas must satisfy OpenAI strict mode
laravel/ai sends structured output schemas with `strict: true` (see `Gateway/OpenAi/Concerns/BuildsTextRequests.php`), so a `HasStructuredOutput` schema only accepts the strict subset. A schema that breaks it fails with a 400, `Invalid schema for response_format 'schema_definition'`.

Every property must be listed in `required`. There are no optional properties — express "may be absent" as `->nullable()->required()`, which emits `"type": ["integer", "null"]`.

Numeric and string constraints are not supported keywords. `->min()`, `->max()` and `->multipleOf()` will be rejected. Enforce ranges in the prompt and validate the response in PHP instead. `enum` is supported and is the reliable way to constrain a value.

To check a schema without burning an API call:
`(new Laravel\Ai\ObjectSchema($agent->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory())))->toSchema()`
