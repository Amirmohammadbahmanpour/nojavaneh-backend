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
        $profile = Profile::where('user_id', $userId)->firstOrFail();
        
        $data = [];
        
        if ($request->has('full_name')) {
            $data['full_name'] = $request->full_name;
        }
        if ($request->has('age')) {
            $data['age'] = $request->age;
        }
        if ($request->has('education')) {
            $data['education'] = $request->education;
        }
        if ($request->has('economic_status')) {
            $data['economic_status'] = $request->economic_status;
        }
        if ($request->has('number_of_children')) {
            $data['number_of_children'] = $request->number_of_children;
        }
        if ($request->has('children_ages')) {
            $data['children_ages'] = $request->children_ages;
        }
        
        $profile->update($data);
        
        return response()->json($profile);
    }
    
    public function show($userId)
    {
        $profile = Profile::where('user_id', $userId)->firstOrFail();
        
        return response()->json([
            'full_name' => $profile->full_name,
            'age' => $profile->age,
            'education' => $profile->education,
            'economic_status' => $profile->economic_status,
            'number_of_children' => $profile->number_of_children,
            'children_ages' => $profile->children_ages,
        ]);
    }
    // دریافت اطلاعات کامل کاربر (برای داشبورد)
    public function dashboard($userId)
    {
        $profile = Profile::where('user_id', $userId)->firstOrFail();
        
        // محاسبه پیشرفت کلی
        $totalLessons = $profile->user->progress()->count();
        $completedLessons = $profile->user->progress()->where('is_completed', true)->count();
        
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