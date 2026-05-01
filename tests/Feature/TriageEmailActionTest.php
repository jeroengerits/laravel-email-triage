<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Actions\TriageEmail;
use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Normalizers\DefaultEmailNormalizer;
use JeroenGerits\EmailTriage\Tests\Fixtures\FakeAiClassifier;

it('uses the default normalizer before classification', function (): void {
    app()->bind(DefaultEmailNormalizer::class, DefaultEmailNormalizer::class);
    app()->bind(EmailTriageClassifier::class, FakeAiClassifier::class);

    $result = app(TriageEmail::class)->handle(new EmailSnapshot(
        subject: ' Original subject ',
        from: 'SomeOne@Example.com ',
        body: "<p>Hello</p>\n\nWorld",
    ));

    expect($result->summary)->toBe('AI triaged Original subject');
});
