<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Tests\Fixtures;

use JeroenGerits\EmailTriage\Classifiers\AiEmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;

final readonly class FakeSpamAiClassifier extends AiEmailTriageClassifier
{
    protected function classifyRaw(EmailSnapshot $email): array
    {
        return [
            'summary' => "AI triaged {$email->subject}",
            'urgency' => 'low',
            'action_needed' => false,
            'action_type' => 'reply',
            'category' => 'support',
            'sentiment' => 'neutral',
            'spam' => true,
            'confidence' => 0.4,
        ];
    }
}
