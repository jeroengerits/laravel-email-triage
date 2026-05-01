<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Actions;

use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\Enums\ActionType;

/**
 * Normalizes the final action based on spam and human-action requirements.
 */
final readonly class DetermineEmailAction
{
    public function handle(TriageResult $result): ActionType
    {
        if ($result->spam) {
            return ActionType::Delete;
        }

        if (! $result->actionNeeded) {
            return ActionType::Archive;
        }

        return $result->actionType;
    }
}
