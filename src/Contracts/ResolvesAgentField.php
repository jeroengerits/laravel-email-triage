<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Contracts;

interface ResolvesAgentField
{
    public function field(): string;
}

