<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\Enums\ActionType;
use JeroenGerits\EmailTriage\Enums\EmailCategory;
use JeroenGerits\EmailTriage\Exceptions\InvalidTriageResultException;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeAiClassifier;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeInvalidAiClassifier;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeSpamAiClassifier;

it('maps structured ai output correctly', function (): void {
    $classifier = new FakeAiClassifier;

    $result = $classifier->classify(new EmailSnapshot(
        subject: 'System outage',
        from: 'ops@example.com',
        body: 'Customers cannot log in.',
    ));

    expect($result->summary)->toBe('AI triaged System outage')
        ->and($result->actionType)->toBe(ActionType::Review)
        ->and($result->category)->toBe(EmailCategory::Support)
        ->and($result->metadata['classifier'])->toBe('ai');
});

it('normalizes action type for spam ai results', function (): void {
    $classifier = new FakeSpamAiClassifier;

    $result = $classifier->classify(new EmailSnapshot(
        subject: 'Spam',
        from: 'spam@example.com',
        body: 'Buy now',
    ));

    expect($result->actionType)->toBe(ActionType::Delete);
});

it('throws on invalid ai output', function (): void {
    $classifier = new FakeInvalidAiClassifier;

    expect(fn (): TriageResult => $classifier->classify(new EmailSnapshot(
        subject: 'Invalid',
        from: 'ops@example.com',
        body: 'Broken payload',
    )))->toThrow(InvalidTriageResultException::class);
});
