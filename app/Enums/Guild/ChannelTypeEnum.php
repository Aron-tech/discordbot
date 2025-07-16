<?php

namespace App\Enums\Guild;

enum ChannelTypeEnum: string
{
    case DEFAULT_LOG = 'default_log';
    case DUTY_LOG = 'duty_log';

    case DUTY = 'duty';

    case ACTIVE_NUM = 'active_num';

    case WARN = 'warn';

    case HOLIDAY = 'holiday';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT_LOG => 'Alapértelmezett Log',
            self::DUTY_LOG => 'Duty Log szoba',
            self::DUTY => 'Duty szoba',
            self::ACTIVE_NUM => 'Aktív szám szoba',
            self::WARN => 'Figyelmeztetés szoba',
            self::HOLIDAY => 'Szabadság szoba',
        };
    }
}
