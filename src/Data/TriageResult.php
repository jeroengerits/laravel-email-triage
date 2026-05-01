<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Data;

use JeroenGerits\EmailTriage\Enums\ActionType;
use JeroenGerits\EmailTriage\Enums\EmailCategory;
use JeroenGerits\EmailTriage\Enums\Sentiment;
use JeroenGerits\EmailTriage\Enums\Urgency;

final readonly class TriageResult
{
    public function __construct(
        public string $summary,
        public Urgency $urgency,
        public ActionType $actionType,
        public bool $actionNeeded,
        public bool $spam,
        public float $confidence,
        public ?EmailCategory $category = null,
        public ?Sentiment $sentiment = null,
        public array $metadata = [],
    ) {}
}
