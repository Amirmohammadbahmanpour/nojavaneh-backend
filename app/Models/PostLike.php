<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PostLike extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'post_likes';  // ✅ اسم جدول درسته؟

    protected $fillable = ['post_id', 'user_id'];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}