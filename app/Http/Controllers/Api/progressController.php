<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProgress;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        // برای دیباگ
        \Log::info('my-progress called', ['user_id' => $request->user()?->id]);
        
        $userId = $request->user()->id;
        $progress = UserProgress::where('user_id', $userId)->get();
        
        return response()->json($progress);
    }
    // ذخیره یا به‌روزرسانی پیشرفت کاربر در یک درس
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'is_completed' => 'required|boolean',
        ]);

        // چک کن درس قفل نباشه
        $lesson = Lesson::findOrFail($request->lesson_id);
        
        if ($lesson->prerequisite_id) {
            $prerequisiteProgress = UserProgress::where('user_id', $request->user()->id)
                ->where('lesson_id', $lesson->prerequisite_id)
                ->first();
            
            if (!$prerequisiteProgress || !$prerequisiteProgress->is_completed) {
                return response()->json([
                    'message' => 'ابتدا باید درس پیش‌نیاز را کامل کنید'
                ], 403);
            }
        }
        

        $progress = UserProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'lesson_id' => $request->lesson_id,
            ],
            [
                'is_completed' => $request->is_completed,
                'completed_at' => $request->is_completed ? now() : null,
            ]
        );

        return response()->json($progress);
    }

    // دریافت آمار پیشرفت کلی کاربر
    public function stats(Request $request)
    {
        $totalLessons = Lesson::where('is_published', true)->count();
        $completedLessons = UserProgress::where('user_id', $request->user()->id)
            ->where('is_completed', true)
            ->count();
        
        // پیشرفت هر دسته‌بندی
        $categoriesProgress = [];
        $categories = \App\Models\Category::with('lessons')->get();
        
        foreach ($categories as $category) {
            $categoryLessons = $category->lessons->where('is_published', true)->count();
            $categoryCompleted = UserProgress::where('user_id', $request->user()->id)
                ->whereIn('lesson_id', $category->lessons->pluck('id'))
                ->where('is_completed', true)
                ->count();
            
            $categoriesProgress[] = [
                'category' => $category->title,
                'total' => $categoryLessons,
                'completed' => $categoryCompleted,
                'percentage' => $categoryLessons > 0 ? round(($categoryCompleted / $categoryLessons) * 100) : 0,
            ];
        }
        
        return response()->json([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'overall_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
            'categories_progress' => $categoriesProgress,
        ]);
    }
}