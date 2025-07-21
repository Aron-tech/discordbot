<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case VIEW_ACTIVE_DUTY = 'view_active_duty';
    case VIEW_YOUR_STATS = 'view_your_stats';
    case VIEW_STATS = 'view_stats';

    case VIEW_BLACKLIST = 'view_blacklist';

    case ADD_BLACKLIST = 'add_blacklist';
    case DELETE_BLACKLIST = 'delete_blacklist';
    case VIEW_ADMIN_PANEL = 'view_admin_panel';
    case VIEW_EXAM_MANAGER = 'view_exam_manager';

    case VIEW_EXAM_RESULT = 'view_exam_result';
    case VIEW_DUTY_ACTIVE = 'view_duty_active';
    case VIEW_DUTY_LOGS = 'view_duty_logs';
    case VIEW_SETTINGS = 'view_settings';
    case USE_AUTO_REPORT = 'use_auto_report';
    case EDIT_PERIOD_DUTY = 'edit_period_duty';
    case DELETE_PERIOD_DUTY = 'delete_period_duty';

    case DELETE_USER_PERIOD_DUTY = 'delete_user_period_duty';
    case DELETE_USER_TOTAL_DUTY = 'delete_user_total_duty';
    case EDIT_TOTAL_DUTY = 'edit_total_duty';
    case DELETE_TOTAL_DUTY = 'delete_total_duty';
    case EDIT_USER_IC_ROLES = 'edit_user_ic_roles';
    case EDIT_USER_IC_DATA = 'edit_user_ic_data';
    case ADD_WARN_TO_USER = 'add_warn_to_user';
    case DELETE_WARN_FROM_USER = 'delete_warn_from_user';
    case DELETE_USER = 'delete_user';
    case EDIT_SETTINGS = 'edit_settings';
    case CHECK_DUTY_LOGS = 'check_duty_logs';
    case DELETE_DUTY = 'delete_duty';
    case DELETE_ACTIVE_DUTY = 'delete_active_duty';

    public static function modPermissions(): array
    {
        return [
            self::VIEW_DUTY_LOGS,
            self::VIEW_ADMIN_PANEL,
            self::VIEW_DUTY_ACTIVE,

            self::VIEW_STATS,
            self::VIEW_YOUR_STATS,
            self::EDIT_PERIOD_DUTY,
            self::EDIT_TOTAL_DUTY,
            self::ADD_WARN_TO_USER,
            self::CHECK_DUTY_LOGS,
            self::VIEW_ACTIVE_DUTY,
            self::DELETE_ACTIVE_DUTY,
        ];
    }

    public static function defaultPermissions(): array
    {
        return [
            self::VIEW_YOUR_STATS,
        ];
    }

}
