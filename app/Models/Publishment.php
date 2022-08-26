<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publishment extends Model
{
    use HasFactory;

    protected $table = 'publishment';

    protected $fillable = [
        'source_platform_id',
        'target_platform_id',
        'video_id',
        'media_id',
        'title',
        'description',
        'is_notify_subscribers',
        'scheduled_time',
        'uploaded_time',
        'upload_trial_times',
        'external_tags',
        'data',
        'status',
    ];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';

    //TODO:: add all model relationships

}
