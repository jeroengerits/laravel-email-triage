<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Facades;

use Illuminate\Support\Facades\Facade;

final class EmailTriage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JeroenGerits\EmailTriage\EmailTriage::class;
    }
}
