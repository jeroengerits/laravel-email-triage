<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Enums;

enum Urgency: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
}
