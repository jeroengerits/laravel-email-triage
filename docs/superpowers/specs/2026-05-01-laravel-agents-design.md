# Laravel Agents Design

Date: 2026-05-01
Topic: Generic Laravel-runtime AI agents for email triage classification

## Goal

Add package-provided agent classes under `src/Agents` that let the package classify emails through multiple specialized AI-oriented agents while preserving the current package-level triage API.

The package must remain vendor-neutral. It should assume a Laravel runtime, but it must not hard-code a specific AI SDK or provider.

## Scope

This design covers:

- Adding specialized agent abstractions and concrete package agents in `src/Agents`
- Adding an orchestrating classifier that composes those agents
- Supporting Laravel container resolution and config-driven agent overrides
- Preserving the existing `EmailTriageClassifier` entrypoint and `TriageResult` output
- Adding tests for orchestration, override behavior, and invalid wiring

This design does not cover:

- Binding to a specific LLM provider or SDK
- Prompt tuning for a particular model vendor
- Multi-step conversational memory or agent loops
- Database persistence, queues, or mailbox integrations

## Recommended Approach

Use multiple specialized package agents behind one orchestrating classifier.

The orchestrating classifier remains the only configured classifier from the consuming app's perspective. Internally, it delegates field-level classification to specialized agents for summary, urgency, spam, category, sentiment, and action-needed assessment. The classifier merges those outputs, derives `actionType` using the existing `DetermineEmailAction` action, and returns a normal `TriageResult`.

This keeps the public package API stable while enabling fine-grained specialization, easier testing, and app-level overrides for individual agents through Laravel config and container resolution.

## Architecture

### Public Entry Point

The package continues to expose one configured classifier through `config/email-triage.php`. Consumers still point `ai.classifier` at a single class that implements `EmailTriageClassifier`.

The new default package implementation will be an orchestrating classifier that:

- Implements `EmailTriageClassifier`
- Accepts agent dependencies through constructor injection
- Delegates to specialized agents
- Builds the final `TriageResult`

### Agent Layer

Add `src/Agents` with a small contract-first structure.

Proposed responsibilities:

- `SummaryAgent`: returns a concise summary string
- `UrgencyAgent`: returns a `Urgency` enum value
- `SpamAgent`: returns a boolean spam decision
- `CategoryAgent`: returns an `EmailCategory` enum value
- `SentimentAgent`: returns a `Sentiment` enum value
- `ActionNeededAgent`: returns a boolean indicating whether human action is needed
- `ConfidenceAgent` or orchestration fallback: returns an overall confidence float

The package should also include a shared base abstraction for Laravel-runtime AI agents. That base class can centralize common concerns such as:

- Access to the `EmailSnapshot`
- Prompt creation hooks
- Normalization helpers
- Consistent exception messages
- Optional metadata contribution

The specialized agents should remain narrow and single-purpose. They should not know about `TriageResult` as a whole.

### Orchestration

Add an orchestrating classifier outside `src/Agents`, likely under `src/Classifiers`, to preserve current package structure.

Its flow:

1. Receive `EmailSnapshot`
2. Resolve or receive specialized agents
3. Call each agent with the same email snapshot
4. Merge the returned fragments into a normalized intermediate payload
5. Derive `actionType` using `DetermineEmailAction`
6. Return `TriageResult`

The intermediate payload should be package-internal rather than a new broad public API. That keeps the contract surface small.

## Contracts

### Agent Contract

Each specialized agent should implement a package contract under `src/Contracts`.

The contract should be intentionally simple:

- Input: `EmailSnapshot`
- Output: a strongly typed scalar or enum for that agent's concern

Avoid a loose array-returning contract for field agents. The current package already uses arrays in `AiEmailTriageClassifier` for whole-result mapping; that is acceptable for a single raw AI payload, but it is a poor fit for narrow field agents. Specialized agents should expose typed outputs to reduce ambiguity and simplify tests.

### Base AI Integration Hook

Because the package is vendor-neutral, package agents should not directly instantiate a model client. Instead, they should rely on Laravel-resolved collaborators or overridable protected methods.

Two acceptable extension patterns:

1. A shared internal contract for "run one AI classification prompt and return normalized data"
2. Abstract package agent classes that consuming apps subclass to connect a model client

The recommended pattern is a shared collaborator contract injected into package agents. That keeps agents concrete and container-friendly while still allowing apps to bind the actual AI implementation.

## Configuration

Extend `config/email-triage.php` to support agent mappings in addition to the top-level classifier.

Proposed shape:

```php
return [
    'ai' => [
        'classifier' => \Vendor\Package\Classifiers\AgentEmailTriageClassifier::class,
        'agents' => [
            'summary' => \Vendor\Package\Agents\SummaryAgent::class,
            'urgency' => \Vendor\Package\Agents\UrgencyAgent::class,
            'spam' => \Vendor\Package\Agents\SpamAgent::class,
            'category' => \Vendor\Package\Agents\CategoryAgent::class,
            'sentiment' => \Vendor\Package\Agents\SentimentAgent::class,
            'action_needed' => \Vendor\Package\Agents\ActionNeededAgent::class,
            'confidence' => \Vendor\Package\Agents\ConfidenceAgent::class,
        ],
    ],
];
```

The orchestrator should resolve configured classes through the container and validate they implement the expected contracts.

## Data Flow

For a call to `EmailTriage::triage($email)`:

1. The configured classifier is resolved.
2. The classifier invokes each specialized agent with the same `EmailSnapshot`.
3. Each agent returns exactly one concern-specific value.
4. The classifier assembles those values into an internal result structure.
5. `DetermineEmailAction` derives the final `actionType` from the assembled result state.
6. The classifier returns `TriageResult` with merged metadata that identifies the orchestrated classifier and optionally the participating agents.

Metadata should remain additive and non-breaking. At minimum, the classifier should continue to mark the result as AI-derived.

## Error Handling

Failure modes must be explicit and early.

- If a configured agent class is missing, empty, or not instantiable, throw `EmailTriageException`
- If a configured class does not implement the expected agent contract, throw `EmailTriageException`
- If an agent returns an invalid value for its field, throw a package exception that names the failing agent and field
- If confidence falls outside `0..1`, treat it as invalid

The orchestrator should fail fast rather than silently substituting defaults. Silent fallback would make triage outcomes harder to trust and test.

## Testing Strategy

Add coverage in three layers.

### Unit Tests

- Agent contract behavior for each specialized agent base or concrete implementation
- Orchestrator merge logic
- `DetermineEmailAction` integration with assembled results
- Invalid return values and exception messages

### Feature Tests

- Container resolution of the orchestrating classifier
- Config-driven agent overrides
- Invalid agent class wiring
- End-to-end `EmailTriage::triage()` behavior with fake agents

### Fixtures

Add test fixtures for:

- Valid fake agents for each concern
- Invalid fake agents returning malformed values
- Misconfigured classes that do not implement the right contract

## File Plan

Expected new or changed areas:

- `src/Agents/*`
- `src/Classifiers/*`
- `src/Contracts/*`
- `config/email-triage.php`
- `tests/Unit/*`
- `tests/Feature/*`

The existing `AiEmailTriageClassifier` should remain available as a separate extension point. The new agent-based orchestration should complement it, not replace it.

## Tradeoffs

Benefits:

- Preserves the current package API
- Keeps responsibilities narrow and testable
- Lets apps override one field agent without replacing the full classifier
- Stays vendor-neutral while still being Laravel-native

Costs:

- More classes and wiring than a single raw classifier
- Potentially more AI calls if each agent independently talks to a model
- Requires clear internal contracts to avoid fragmentation

The design accepts that cost because the user explicitly wants multiple specialized agents. If call count becomes a real concern later, a batched internal AI collaborator can optimize execution without changing the agent-facing contract.

## Open Decisions Resolved

- Runtime assumption: Laravel runtime is assumed
- Provider coupling: none
- Agent granularity: multiple specialized agents
- Public API: keep a single configured classifier entrypoint

## Acceptance Criteria

The design is successful when:

- `src/Agents` contains package-defined specialized agents
- The package still exposes one configured classifier implementing `EmailTriageClassifier`
- That classifier composes the specialized agents and returns `TriageResult`
- Consumers can override individual agent classes through config
- Invalid agent wiring fails with clear package exceptions
- Tests cover orchestration and override behavior
