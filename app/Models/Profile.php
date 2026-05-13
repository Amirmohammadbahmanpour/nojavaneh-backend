<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Profile extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'age',
        'education',
        'economic_status',
        'number_of_children',
        'children_ages',  // ← این خط حتماً باشه
        'role'
    ];

    protected $casts = [
        'age' => 'integer',
        'number_of_children' => 'integer',
        'children_ages' => 'array',
        'has_attended_workshop' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}