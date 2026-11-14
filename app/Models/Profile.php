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
        'children_ages',
        'role',
        'mother_age',
        'mother_education',
        'father_education',
        'mother_employment_status',
        'family_income_status',
        'number_of_teenagers',
        'teenager_gender',
        'teenager_age_range',
        'has_attended_parenting_course',
        'has_attended_workshop',
        'teenager_phone_internet_usage',
        'teenager_living_with',
    ];

    protected $casts = [
        'age' => 'integer',
        'mother_age' => 'integer',
        'number_of_children' => 'integer',
        'number_of_teenagers' => 'integer',
        'children_ages' => 'array',
        'has_attended_workshop' => 'boolean',
        'has_attended_parenting_course' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
