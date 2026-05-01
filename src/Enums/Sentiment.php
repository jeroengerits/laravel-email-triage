<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Enums;

enum Sentiment: string
{
    case Positive = 'positive';
    case Neutral = 'neutral';
    case Negative = 'negative';
    case Mixed = 'mixed';
}
