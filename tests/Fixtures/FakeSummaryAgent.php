<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Tests\Fixtures;

use JeroenGerits\EmailTriage\Contracts\SummarizesEmail;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;

final class FakeSummaryAgent implements SummarizesEmail
{
    public function respond(EmailSnapshot $email): string
    {
        return 'summary for '.$email->subject;
    }

    public function field(): string
    {
        return 'summary';
    }
}

