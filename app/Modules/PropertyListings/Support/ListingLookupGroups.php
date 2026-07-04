<?php

namespace App\Modules\PropertyListings\Support;

class ListingLookupGroups
{
    public const IMAGE_TYPE = 'image_type';

    public const PROPERTY_TYPE = 'property_type';

    public const COMPLETION_STATUS = 'completion_status';

    public const PROPERTY_USE = 'property_use';

    public const HANDOVER_CONDITION = 'handover_condition';

    public const AVAILABILITY = 'availability';

    public const BEDROOMS = 'bedrooms';

    public const GRADE = 'grade';

    public const FEE_TYPE = 'fee_type';

    public const PEZA_ACCREDITATION = 'peza_accreditation';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::IMAGE_TYPE => 'Image / Asset Types',
            self::PROPERTY_TYPE => 'Property Types',
            self::COMPLETION_STATUS => 'Completion Status',
            self::PROPERTY_USE => 'Property Use',
            self::HANDOVER_CONDITION => 'Handover Condition',
            self::AVAILABILITY => 'Availability',
            self::BEDROOMS => 'Bedrooms',
            self::GRADE => 'Grade',
            self::FEE_TYPE => 'Fee Types',
            self::PEZA_ACCREDITATION => 'PEZA Accreditation',
        ];
    }

    public static function label(string $group): string
    {
        return self::labels()[$group] ?? $group;
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }

    public static function has(string $group): bool
    {
        return array_key_exists($group, self::labels());
    }

    public static function usesFileKind(string $group): bool
    {
        return $group === self::IMAGE_TYPE;
    }
}
