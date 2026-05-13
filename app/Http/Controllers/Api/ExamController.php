<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserExam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    // دریافت آخرین نتیجه آزمون
public function latest(Request $request)
{
    $request->validate([
        'exam_type' => 'required|in:pretest,posttest',
    ]);

    $exam = UserExam::where('user_id', $request->user()->id)
        ->where('exam_type', $request->exam_type)
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$exam) {
        return response()->json(['message' => 'آزمونی یافت نشد'], 404);
    }

    // محاسبه تعداد سوالات (از فایل سوالات)
    $totalQuestions = $request->exam_type === 'pretest' ? 10 : 10; // عدد واقعی رو بذار

    return response()->json([
        'id' => $exam->id,
        'score' => $exam->score,
        'is_passed' => $exam->is_passed,
        'exam_type' => $exam->exam_type,
        'created_at' => $exam->created_at,
        'total_questions' => $totalQuestions,
    ]);
}
    // دریافت همه آزمون‌های کاربر جاری
    public function index(Request $request)
    {
        $exams = UserExam::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($exams);
    }

    // بررسی اینکه کاربر قبلاً آزمون داده یا نه
    public function check(Request $request)
    {
        $request->validate([
            'exam_type' => 'required|in:pretest,posttest',
        ]);

        $exists = UserExam::where('user_id', $request->user()->id)
            ->where('exam_type', $request->exam_type)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    // ثبت نمره آزمون
    public function store(Request $request)
    {
        $request->validate([
            'exam_type' => 'required|in:pretest,posttest',
            'score' => 'required|integer|min:0|max:100',
            'course_id' => 'nullable|string',
            'is_passed' => 'boolean',
        ]);

        $existing = UserExam::where('user_id', $request->user()->id)
            ->where('exam_type', $request->exam_type)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'شما قبلاً در این آزمون شرکت کرده‌اید'], 409);
        }

        $exam = UserExam::create([
            'user_id' => $request->user()->id,
            'exam_type' => $request->exam_type,
            'score' => $request->score,
            'course_id' => $request->course_id,
            'is_passed' => $request->is_passed ?? ($request->score >= 60),
        ]);

        return response()->json($exam, 201);
    }
}