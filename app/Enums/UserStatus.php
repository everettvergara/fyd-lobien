<?php

namespace App\Enums;

enum UserStatus: string
{
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => 'Pending Verification',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
            self::Locked => 'Locked',
        };
    }

    public function canLogin(): bool
    {
        return $this === self::Active;
    }
}
