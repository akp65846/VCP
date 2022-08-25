<?php

namespace App\Enum;

class PlatformConstant
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const GROUP_YOUTUBE = 'youtube';
    const GROUP_TIKTOK = 'tiktok';

    const TYPE_SOURCE = 1;
    const TYPE_TARGET = 2;

    /**
     * @return int[]
     */
    public static function allStatus(): array {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE
        ];
    }

    /**
     * @return string[]
     */
    public static function allGroup(): array
    {
        return [
            self::GROUP_TIKTOK,
            self::GROUP_YOUTUBE,
        ];
    }

    /**
     * @return string[]
     */
    public static function allType(): array
    {
        return [
            self::TYPE_SOURCE,
            self::TYPE_TARGET
        ];
    }
}
