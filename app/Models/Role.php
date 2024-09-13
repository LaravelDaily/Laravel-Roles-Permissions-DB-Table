<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public const ROLE_USER = 1;
    public const ROLE_ADMIN = 2;
    public const ROLE_MANAGER = 3;
}
