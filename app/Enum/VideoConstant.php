<?php

namespace App\Enum;

class VideoConstant
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_INVALID = 3;

    const APPROVAL_STATUS_PENDING = 1;
    const APPROVAL_STATUS_APPROVED = 2;
    const APPROVAL_STATUS_REJECTED = 3;

    /**
     * @return string[]
     */
    public function allStatus(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_INVALID
        ];
    }
}
