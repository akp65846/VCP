<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $table = 'video';

    protected $fillable = [
        'platform_id',
        'content_creator_id',
        'key',
        'source_url',
        'media_id',
        'status',
        'approval_status',
        'approval_time',
        'cover_image_url',
        'title',
        'size',
        'height',
        'duration',
        'width',
        'remarks',
    ];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';

    public function contentCreator() {
        return $this->hasOne(ContentCreator::class, 'content_creator_id');
    }

    //TODO:: add all model relationships

}
