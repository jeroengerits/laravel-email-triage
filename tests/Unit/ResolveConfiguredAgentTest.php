<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Actions\ResolveConfiguredAgent;
use JeroenGerits\EmailTriage\Exceptions\EmailTriageException;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeNonAgent;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeSummaryAgent;

it('resolves the configured agent implementation', function (): void {
    config()->set('email-triage.ai.summary_agent', FakeSummaryAgent::class);

    expect(app(ResolveConfiguredAgent::class)->fromConfig('ai.summary_agent'))
        ->toBeInstanceOf(FakeSummaryAgent::class);
});

it('throws when agent config is missing', function (): void {
    config()->set('email-triage.ai.summary_agent');

    expect(fn () => app(ResolveConfiguredAgent::class)->fromConfig('ai.summary_agent'))
        ->toThrow(EmailTriageException::class, 'must be a class name');
});

it('throws when agent config references a non-agent class', function (): void {
    config()->set('email-triage.ai.summary_agent', FakeNonAgent::class);

    expect(fn () => app(ResolveConfiguredAgent::class)->fromConfig('ai.summary_agent'))
        ->toThrow(EmailTriageException::class, 'must implement');
});

