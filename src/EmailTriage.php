<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage;

use JeroenGerits\EmailTriage\Actions\TriageEmail;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;

final readonly class EmailTriage
{
    public function __construct(
        private TriageEmail $triageEmail,
    ) {}

    public function triage(EmailSnapshot $email): TriageResult
    {
        return $this->triageEmail->handle($email);
    }
}
