<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\EmailTriage;
use JeroenGerits\EmailTriage\EmailTriageServiceProvider;
use JeroenGerits\EmailTriage\Exceptions\EmailTriageException;
use JeroenGerits\EmailTriage\Exceptions\InvalidTriageResultException;
use JeroenGerits\EmailTriage\Facades\EmailTriage as EmailTriageFacade;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeAiClassifier;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeInvalidAiClassifier;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeNonClassifier;

it('binds the email triage service', function (): void {
    config()->set('email-triage.ai.classifier', FakeAiClassifier::class);

    expect(app(EmailTriage::class))->toBeInstanceOf(EmailTriage::class);
});

it('exposes a publishable config path', function (): void {
    $paths = ServiceProvider::pathsToPublish(EmailTriageServiceProvider::class, 'email-triage-config');

    expect(array_key_first($paths))->toEndWith('/config/email-triage.php')
        ->and(array_values($paths)[0])->toEndWith('/config/email-triage.php');
});

it('returns a triage result from the facade', function (): void {
    config()->set('email-triage.ai.classifier', FakeAiClassifier::class);

    $result = EmailTriageFacade::triage(new EmailSnapshot(
        subject: 'Invoice overdue',
        from: 'billing@example.com',
        body: 'Please pay within 3 days to avoid suspension.',
        inbox: 'billing',
    ));

    expect($result)->toBeInstanceOf(TriageResult::class)
        ->and($result->summary)->toBe('AI triaged Invoice overdue')
        ->and($result->metadata['classifier'])->toBe('ai');
});

it('resolves the configured ai classifier implementation', function (): void {
    config()->set('email-triage.ai.classifier', FakeAiClassifier::class);

    expect(app(EmailTriageClassifier::class))->toBeInstanceOf(FakeAiClassifier::class);
});

it('classifies email with the configured ai classifier', function (): void {
    config()->set('email-triage.ai.classifier', FakeAiClassifier::class);

    $result = app(EmailTriageClassifier::class)->classify(new EmailSnapshot(
        subject: 'System outage',
        from: 'ops@example.com',
        body: 'Customers cannot log in.',
    ));

    expect($result->summary)->toBe('AI triaged System outage')
        ->and($result->metadata['classifier'])->toBe('ai');
});

it('throws when ai classifier config is missing', function (): void {
    config()->set('email-triage.ai.classifier');

    expect(fn () => app(EmailTriageClassifier::class))
        ->toThrow(EmailTriageException::class, 'must be a class name');
});

it('throws when ai classifier config references a missing class', function (): void {
    config()->set('email-triage.ai.classifier', 'not-a-class');

    expect(fn () => app(EmailTriageClassifier::class))
        ->toThrow(EmailTriageException::class, 'must reference an existing class');
});

it('throws when ai classifier config references a non-classifier class', function (): void {
    config()->set('email-triage.ai.classifier', FakeNonClassifier::class);

    expect(fn () => app(EmailTriageClassifier::class))
        ->toThrow(EmailTriageException::class, 'must implement '.EmailTriageClassifier::class);
});

it('propagates invalid ai payloads during facade triage', function (): void {
    config()->set('email-triage.ai.classifier', FakeInvalidAiClassifier::class);

    expect(fn () => EmailTriageFacade::triage(new EmailSnapshot(
        subject: 'Broken AI output',
        from: 'ops@example.com',
        body: 'Classifier returned invalid payload.',
    )))->toThrow(InvalidTriageResultException::class);
});
