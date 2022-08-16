<?php

namespace App\Enum;

class MediaConstant {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @return int[]
     */
    public static function allStatus(): array {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE
        ];
    }
}
