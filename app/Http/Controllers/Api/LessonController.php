<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    // دریافت همه دروس (با فیلتر دسته‌بندی)
    public function index(Request $request)
    {
        $query = Lesson::with('category')->where('is_published', true);
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $lessons = $query->orderBy('order_index')->get();
        
        // اضافه کردن وضعیت تکمیل برای هر درس
        $userId = auth('sanctum')->id() ?? auth()->id();
        if ($userId) {
            $completedLessons = UserProgress::where('user_id', $userId)
                ->where('is_completed', true)
                ->pluck('lesson_id')
                ->toArray();
                
            foreach ($lessons as $lesson) {
                $lesson->is_completed = in_array($lesson->id, $completedLessons);
            }
        } else {
            foreach ($lessons as $lesson) {
                $lesson->is_completed = false;
            }
        }
        
        return response()->json($lessons);
    }

    // دریافت جزئیات یک درس
    public function show(Request $request, $id)
    {
        $lesson = Lesson::with('category')->findOrFail($id);
        
        // اضافه کردن وضعیت تکمیل برای کاربر جاری
        $userId = auth('sanctum')->id() ?? auth()->id();
        $isCompleted = false;
        
        if ($userId) {
            $progress = UserProgress::where('user_id', $userId)
                ->where('lesson_id', $id)
                ->first();
            $isCompleted = $progress && $progress->is_completed === true;
        }
        
        // تبدیل lesson به آرایه و اضافه کردن فیلد is_completed
        $lessonArray = $lesson->toArray();
        $lessonArray['is_completed'] = $isCompleted;
        
        // اضافه کردن وضعیت قفل (اگر درس پیش‌نیاز دارد یا درس قبلی کامل نشده)
        $isLocked = false;
        $prerequisiteTitle = null;

        if ($userId) {
            if ($lesson->prerequisite_id) {
                $prerequisite = Lesson::find($lesson->prerequisite_id);
                $prerequisiteProgress = UserProgress::where('user_id', $userId)
                    ->where('lesson_id', $lesson->prerequisite_id)
                    ->first();
                $isLocked = !($prerequisiteProgress && $prerequisiteProgress->is_completed === true);
                $prerequisiteTitle = $prerequisite ? $prerequisite->title : null;
            } else {
                $prevLesson = Lesson::where('category_id', $lesson->category_id)
                    ->where('is_published', true)
                    ->where('order_index', '<', $lesson->order_index)
                    ->orderBy('order_index', 'desc')
                    ->first();

                if ($prevLesson) {
                    $prevProgress = UserProgress::where('user_id', $userId)
                        ->where('lesson_id', $prevLesson->id)
                        ->first();
                    $isLocked = !($prevProgress && $prevProgress->is_completed === true);
                    $prerequisiteTitle = $prevLesson->title;
                }
            }
        }

        $lessonArray['is_locked'] = $isLocked;
        $lessonArray['prerequisite_title'] = $isLocked ? $prerequisiteTitle : null;
        
        return response()->json($lessonArray);
    }

    // دریافت پیشرفت کاربر در همه دروس
    public function progress(Request $request)
    {
        $userId = $request->user()->id;
        $progress = UserProgress::where('user_id', $userId)->get();
        
        return response()->json($progress);
    }
}
