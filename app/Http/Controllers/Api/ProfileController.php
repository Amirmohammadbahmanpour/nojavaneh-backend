<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // دریافت پروفایل کاربر
    public function update(Request $request, $userId)
    {
        $profile = Profile::firstOrCreate(
            ['user_id' => $userId],
            ['role' => 'user']
        );
        
        $fields = [
            'full_name', 'age', 'education', 'economic_status', 'number_of_children', 'children_ages',
            'mother_age', 'mother_education', 'father_education', 'mother_employment_status',
            'family_income_status', 'number_of_teenagers', 'teenager_gender', 'teenager_age_range',
            'has_attended_parenting_course', 'teenager_phone_internet_usage', 'teenager_living_with'
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }
        
        $profile->update($data);
        
        return response()->json($profile);
    }
    
    public function show($userId)
    {
        $profile = Profile::firstOrCreate(
            ['user_id' => $userId],
            ['role' => 'user']
        );
        
        return response()->json($profile);
    }

    // دریافت اطلاعات کامل کاربر (برای داشبورد)
    public function dashboard($userId)
    {
        $profile = Profile::firstOrCreate(
            ['user_id' => $userId],
            ['role' => 'user']
        );
        
        // محاسبه پیشرفت کلی
        $totalLessons = $profile->user ? $profile->user->progress()->count() : 0;
        $completedLessons = $profile->user ? $profile->user->progress()->where('is_completed', true)->count() : 0;
        
        return response()->json([
            'profile' => $profile,
            'stats' => [
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
            ]
        ]);
    }
}
