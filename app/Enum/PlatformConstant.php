<?php

namespace App\Enum;

use JetBrains\PhpStorm\ArrayShape;

class PlatformConstant
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const NAME_YOUTUBE = 'youtube';

    /**
     * @return string[]
     */
    public function allStatus(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive'
        ];
    }
}
