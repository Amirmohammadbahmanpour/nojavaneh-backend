<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lesson extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'category_id', 'title', 'description', 'content_text',
        'featured_image_url', 'video_url', 'podcast_url', 'video_type',
        'order_index', 'estimated_time', 'prerequisite_id', 'is_published'
    ];

    protected $casts = [
        'order_index' => 'integer',
        'estimated_time' => 'integer',
        'is_published' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function prerequisite()
    {
        return $this->belongsTo(Lesson::class, 'prerequisite_id');
    }

    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function likes()
    {
        return $this->hasMany(LessonLike::class);
    }
}