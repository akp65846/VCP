<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCreator extends Model
{
    use HasFactory;

    protected $table = 'content_creator';

    protected $fillable = ['platform_id', 'platform_unique_uid', 'name', 'profile_url', 'profile_icon_url', 'max_video_duration', 'status', 'last_processed_time'];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';

    public function platform() {
        return $this->hasOne(Platform::class, 'platform_id');
    }
}
