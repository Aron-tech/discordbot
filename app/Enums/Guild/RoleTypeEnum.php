<?php

namespace App\Enums\Guild;

enum RoleTypeEnum: string
{
    case ADMIN_ROLES = 'admin_roles';
    case MOD_ROLES = 'mod_roles';
    case DEFAULT_ROLES = 'default_roles';
    case IC_ROLES = 'ic_roles';
    case WARN_ROLES = 'warn_roles';

    case DUTY_ROLE = 'duty_role';

    case FREEDOM_ROLE = 'freedom_role';
}
