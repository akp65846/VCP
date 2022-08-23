<?php

namespace App\Enum;

class PlatformConstant
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const NAME_YOUTUBE = 'youtube';
    const NAME_TIKTOK = 'tiktok';

    const GROUP_YOUTUBE = 'youtube';
    const GROUP_TIKTOK = 'tiktok';


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
    public static function allName(): array
    {
        return [
          self::NAME_YOUTUBE,
          self::NAME_TIKTOK
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
}
