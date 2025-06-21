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
            self::DEFAULT_LOG => 'Default Log',
            self::DUTY_LOG => 'Duty Log',
            self::DUTY => 'Duty Channel',
            self::ACTIVE_NUM => 'Active Duty Number Channel',
            self::MEMBER_INFO => 'Member Info Channel',
        };
    }
}
