<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Data\EmailSnapshot;

it('can create an email snapshot', function (): void {
    $email = new EmailSnapshot(
        subject: 'Invoice overdue',
        from: 'billing@example.com',
        body: 'Please pay within 3 days.',
        inbox: 'billing',
    );

    expect($email->subject)->toBe('Invoice overdue')
        ->and($email->from)->toBe('billing@example.com')
        ->and($email->inbox)->toBe('billing');
});
