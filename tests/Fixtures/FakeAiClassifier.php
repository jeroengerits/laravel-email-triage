<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Tests\Fixtures;

use JeroenGerits\EmailTriage\Classifiers\AiEmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;

final readonly class FakeAiClassifier extends AiEmailTriageClassifier
{
    protected function classifyRaw(EmailSnapshot $email): array
    {
        return [
            'summary' => "AI triaged {$email->subject}",
            'urgency' => 'high',
            'action_needed' => true,
            'action_type' => 'review',
            'category' => 'support',
            'sentiment' => 'negative',
            'spam' => false,
            'confidence' => 0.82,
        ];
    }
}
