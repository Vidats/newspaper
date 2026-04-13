<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'summary',
        'slug',
        'user_id',
        'menu_id',
        'media_id',
    ];
public function user()
{
    return $this->belongsTo(User::class);
}

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }}
