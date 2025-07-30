<?php

namespace App\Enums\Guild;

enum SettingTypeEnum: string
{
    case MIN_RANK_UP_DUTY = 'rank_up_duty';
    case MIN_RANK_UP_TIME = 'rank_up_time';

    case MIN_DUTY = 'min_duty';
    case WARN_TIME = 'warn_time';
}

