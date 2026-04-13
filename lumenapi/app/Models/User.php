<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject; // MỚI THÊM

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject // MỚI THÊM
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password'];

    // 2 HÀM BẮT BUỘC CỦA JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
public function posts()
    {
        return $this->hasMany(Post::class);
    }

    }
