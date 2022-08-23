<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $table = 'platform';

    protected $fillable = ['name', 'group', 'url', 'description', 'status'];
    protected $guarded = ['status'];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
}
