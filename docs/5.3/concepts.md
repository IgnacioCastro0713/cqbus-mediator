# Core Concepts

This package supports two main architectural patterns out of the box. Both are driven by the central `Mediator` service.

### 1. Command / Query Pattern (1-to-1)
This pattern is used when a single Request (representing a Command to change state, or a Query to read state) is handled by exactly **one** Handler.

- You dispatch the request using `$mediator->send($request)`.
- The Mediator passes the request through any configured **Pipelines** (middleware).
- The request reaches its dedicated **Handler**, which executes the business logic.
- The Handler returns data back to the caller.

### 2. Event Bus Pattern (1-to-N)
This pattern is used for side-effects or broadcasting. An Event is published, and **multiple** Event Handlers can react to it.

- You broadcast the event using `$mediator->publish($event)`.
- The Mediator finds all Handlers listening to that specific Event class.
- It executes them in order of their **priority** (highest first).
- Returns an array of responses from all executed handlers.
