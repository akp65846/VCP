<?php

namespace App\Enum;

class ContentCreatorConstant
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @return string[]
     */
    public static function allStatus(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE
        ];
    }
}
