<?php

namespace App\Enums\Guild;

enum ChannelTypeEnum: string
{
    case DEFAULT_LOG = 'default_log';
    case DUTY_LOG = 'duty_log';

    case DUTY = 'duty';

    case ACTIVE_NUM = 'active_num';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT_LOG => 'Alapértelmezett Log',
            self::DUTY_LOG => 'Szolgálati Log',
            self::DUTY => 'Szolgálati szoba',
            self::ACTIVE_NUM => 'Szolgálatban lévők számát megjelenítő szoba',
        };
    }
}
