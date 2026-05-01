<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Contracts;

use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;

interface EmailTriageClassifier
{
    public function classify(EmailSnapshot $email): TriageResult;
}
