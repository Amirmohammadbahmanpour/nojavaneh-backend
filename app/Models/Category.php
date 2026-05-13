<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title', 'slug', 'icon_name', 'order_index', 'image_url'
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}