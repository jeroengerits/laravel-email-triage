<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Enums;

enum ActionType: string
{
    case None = 'none';
    case Reply = 'reply';
    case Schedule = 'schedule';
    case Payment = 'payment';
    case Review = 'review';
    case FollowUp = 'follow_up';
    case Archive = 'archive';
    case Delete = 'delete';
}
