<?php

namespace App\Enums\Guild;

enum SettingTypeEnum: string
{
    case MIN_RANK_UP_DUTY = 'rank_up_duty';
    case MIN_RANK_UP_TIME = 'rank_up_time';

    case NEXT_CHECKING_TIME = 'next_checking_time';

    case MIN_DUTY = 'min_duty';
    case WARN_TIME = 'warn_time';

    //Systems boolean
    case DUTY_SYSTEM = 'duty_system';
    case WARN_SYSTEM = 'warn_system';
    case RANK_SYSTEM = 'rank_system';
    case HOLIDAY_SYSTEM = 'holiday_system';
    case CHECK_SYSTEM = 'check_system';
    case BLACKLIST_SYSTEM = 'blacklist_system';
    case EXAM_SYSTEM = 'exam_system';
    case STATISTIC_SYSTEM = 'statistic_system';


}

