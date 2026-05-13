<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BlogPost extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    protected $table = 'blog_posts';  // ✅ مطمئن شو این هست
    public $incrementing = false;

    protected $fillable = [
        'title', 'slug', 'content', 'image_url', 'description', 
        'author_id', 'is_published', 'published_at'
    ];
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }
}