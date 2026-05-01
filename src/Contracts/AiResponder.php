<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Contracts;

use JeroenGerits\EmailTriage\Data\EmailSnapshot;

interface AiResponder
{
    public function respond(EmailSnapshot $email): mixed;
}

