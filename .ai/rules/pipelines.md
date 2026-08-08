---
paths:
  - 'app/Pipelines/**'
---

# Pipelines

## Pipeline + Steps for multi-stage flows
Multi-stage flows use a `*Pipeline` class with a public `run()` method that sends a DataObject through `app(Pipeline::class)->send($data)->through($pipes)->thenReturn()`.

Each step is a class in a sibling `Steps/` directory with `handle(mixed $data, Closure $next): mixed`. Inject the pipeline into the controller and call `run()`.
