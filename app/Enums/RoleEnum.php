<?php
declare (strict_types = 1);

namespace App\Enums;

enum RoleEnum: int
{
    case ADMIN = 1;
    case USER = 2;

    /**
     * Check if the given role is an admin role.
     *
     * @param int $role The role to be checked.
     * @return bool True if the role is admin, false otherwise.
     */
    public static function isAdmin(RoleEnum $role): bool
    {
        return $role === self::ADMIN;
    }
}
