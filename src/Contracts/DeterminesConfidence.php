<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Contracts;

interface DeterminesConfidence extends AiResponder, ResolvesAgentField {}

