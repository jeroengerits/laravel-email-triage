<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Contracts;

interface DeterminesActionNeeded extends AiResponder, ResolvesAgentField {}

