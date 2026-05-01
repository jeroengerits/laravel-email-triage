# Laravel Email Triage

A small Laravel package for AI-only classification of incoming emails into urgency, action type, category, sentiment, and spam status.

It is designed for inbox automation, support tooling, personal assistants, CRM workflows, and AI-powered email processing without coupling your app to one mailbox provider or one AI vendor.

## Features

- AI-only email triage
- Provider-agnostic AI classifier integration
- Stable DTO result object
- Laravel facade and action API
- Swappable classifier contract
- Pest and Testbench support
- No database required
- No mailbox provider lock-in

## Status

Ready for local package development and testbench-based verification.

## Installation

```bash
composer require jeroengerits/laravel-email-triage
```

Publish the config and register a concrete AI classifier before using the package:

```bash
php artisan vendor:publish --tag=email-triage-config
```

## Configuration

This package is intentionally AI-only. You must configure `ai.classifier` in `config/email-triage.php`.

```php
// config/email-triage.php
'ai' => [
    'classifier' => App\Mail\AppAiTriageClassifier::class,
],
```

If `ai.classifier` is missing, empty, invalid, or points to a class that does not implement `EmailTriageClassifier`, the package throws `EmailTriageException` when the classifier is resolved.

## Basic Usage

```php
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Facades\EmailTriage;

$result = EmailTriage::triage(new EmailSnapshot(
    subject: 'Invoice overdue',
    from: 'billing@example.com',
    body: 'Please pay within 3 days to avoid suspension.',
    inbox: 'billing',
));
```

Example result with a configured AI classifier:

```php
[
    'summary' => 'Customer reported a login outage.',
    'urgency' => 'high',
    'actionType' => 'review',
    'actionNeeded' => true,
    'spam' => false,
    'confidence' => 0.91,
    'category' => 'support',
    'sentiment' => 'negative',
    'metadata' => [
        'classifier' => 'ai',
    ],
]
```

Or resolve the action directly:

```php
use JeroenGerits\EmailTriage\Actions\TriageEmail;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;

$result = app(TriageEmail::class)->handle(new EmailSnapshot(
    subject: 'Need answer ASAP',
    from: 'client@example.com',
    body: 'Can you confirm before today ends?',
));
```

## Result Shape

`TriageResult` gives you a stable output object with:

- `summary`
- `urgency`
- `actionType`
- `actionNeeded`
- `spam`
- `confidence`
- `category`
- `sentiment`
- `metadata`

## Extending Classifiers

The package contract surface is intentionally small:

- `EmailTriageClassifier`

Consumer-facing extension points:

- `EmailTriageClassifier`
- `AiEmailTriageClassifier`

Everything else should be treated as internal package implementation detail.

If your provider already returns structured output in the package shape, extend `AiEmailTriageClassifier` and implement `classifyRaw()`:

```php
use JeroenGerits\EmailTriage\Classifiers\AiEmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;

final class AppAiTriageClassifier extends AiEmailTriageClassifier
{
    protected function classifyRaw(EmailSnapshot $email): array
    {
        return [
            'summary' => 'Customer reported a login outage.',
            'urgency' => 'high',
            'action_needed' => true,
            'action_type' => 'review',
            'category' => 'support',
            'sentiment' => 'negative',
            'spam' => false,
            'confidence' => 0.91,
        ];
    }
}
```

Invalid structured AI output throws `InvalidTriageResultException`.

## Philosophy

This package does not own the inbox.

It only answers:

> What kind of email is this, how urgent is it, and does a human need to act?
