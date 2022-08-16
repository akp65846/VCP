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
        'scheduled_time',
        'data',
        'status',
    ];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';

    //TODO:: add all model relationships

}
