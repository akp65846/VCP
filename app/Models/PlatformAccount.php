<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformAccount extends Model
{
    use HasFactory;

    protected $table = 'platform_account';

    protected $fillable = ['platform_id', 'name', 'value', 'expire_time'];

    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
}
