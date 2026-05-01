<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Normalizers\DefaultEmailNormalizer;

it('trims subject and body, strips html, and lowercases sender fields', function (): void {
    $normalizer = app(DefaultEmailNormalizer::class);

    $normalized = $normalizer->normalize(new EmailSnapshot(
        subject: '  Hello  ',
        from: '  SENDER@Example.COM ',
        body: " <p>Hi</p>\n\n<strong>Team</strong> ",
        to: '  TO@Example.COM ',
        cc: '  CC@Example.COM ',
        replyTo: '  REPLY@Example.COM ',
    ));

    expect($normalized->subject)->toBe('Hello')
        ->and($normalized->from)->toBe('sender@example.com')
        ->and($normalized->body)->toBe('Hi'."\n".'Team')
        ->and($normalized->to)->toBe('to@example.com')
        ->and($normalized->cc)->toBe('cc@example.com')
        ->and($normalized->replyTo)->toBe('reply@example.com');
});

it('normalizes empty and multiline content safely', function (): void {
    $normalizer = app(DefaultEmailNormalizer::class);

    $normalized = $normalizer->normalize(new EmailSnapshot(
        subject: '   ',
        from: ' Sender@Example.COM ',
        body: "<div>Hello</div>\n\n\n<p>World</p>\n",
        to: null,
        inbox: ' support ',
    ));

    expect($normalized->subject)->toBe('')
        ->and($normalized->from)->toBe('sender@example.com')
        ->and($normalized->body)->toBe("Hello\nWorld")
        ->and($normalized->inbox)->toBe('support');
});
