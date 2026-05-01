<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Enums;

enum EmailCategory: string
{
    case Personal = 'personal';
    case Customer = 'customer';
    case Sales = 'sales';
    case Billing = 'billing';
    case Support = 'support';
    case Legal = 'legal';
    case Newsletter = 'newsletter';
    case Notification = 'notification';
    case Spam = 'spam';
    case Unknown = 'unknown';
}
