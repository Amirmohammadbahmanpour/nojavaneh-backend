<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ExamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========== مسیرهای احراز هویت (عمومی) ==========
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/register-otp', [AuthController::class, 'registerWithOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-reset-code', [AuthController::class, 'sendResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ========== مسیرهای عمومی ==========
// دسته‌بندی‌ها و دروس
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/lessons', [CategoryController::class, 'lessons']);
Route::get('/lessons', [LessonController::class, 'index']);
Route::get('/lessons/{id}', [LessonController::class, 'show']);  // ✅ اضافه شد

// وبلاگ (عمومی)
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);
Route::get('/blog/{id}/related', [BlogController::class, 'related']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/posts/{id}/likes-count', [LikeController::class, 'getPostLikesCount']);
Route::get('/comments/{id}/likes-count', [LikeController::class, 'getCommentLikesCount']);

// ========== مسیرهای محافظت شده (نیاز به لاگین) ==========
Route::middleware('auth:sanctum')->group(function () {
    
    // احراز هویت
    Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // پروفایل
    Route::get('/profiles/{userId}', [ProfileController::class, 'show']);
    Route::put('/profiles/{userId}', [ProfileController::class, 'update']);
    Route::get('/dashboard/{userId}', [ProfileController::class, 'dashboard']);
    
    // دروس و پیشرفت
    Route::get('/my-progress', [ProgressController::class, 'index']);
    Route::get('/user-progress', [ProgressController::class, 'index']); // Alias
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::get('/progress-stats', [ProgressController::class, 'stats']);

    // آپلود تصویر
    Route::post('/upload/image', [AdminController::class, 'uploadImage']);
    
    // نظرات
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    
    // لایک‌ها
    Route::post('/like/lesson', [LikeController::class, 'toggleLesson']);
    Route::post('/like/post', [LikeController::class, 'togglePost']);
    Route::post('/like/comment', [LikeController::class, 'toggleComment']);
    
    // چک کردن لایک
    Route::get('/like/post/check', [LikeController::class, 'checkPostLike']);
    Route::get('/like/comment/check', [LikeController::class, 'checkCommentLike']);
    
    // آزمون‌ها
    Route::get('/user-exams', [ExamController::class, 'index']);
    Route::get('/user-exams/check', [ExamController::class, 'check']);
    Route::post('/user-exams', [ExamController::class, 'store']);
    Route::get('/user-exams/latest', [ExamController::class, 'latest']);
    
    // ✅ مسیر تست برای دیباگ
    Route::get('/debug-progress', [ProgressController::class, 'index']);
});

// ========== مسیرهای ادمین ==========
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    
    // آمار
    Route::get('/stats', [AdminController::class, 'stats']);
    
    // مدیریت کاربران
    Route::get('/users', [AdminController::class, 'users']);
    Route::put('/users/{id}/role', [AdminController::class, 'changeRole']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    
    // مدیریت نظرات
    Route::get('/comments', [AdminController::class, 'comments']);
    Route::put('/comments/{id}/approve', [AdminController::class, 'approveComment']);
    Route::delete('/comments/{id}', [AdminController::class, 'deleteComment']);
    
    // مدیریت پست‌ها
    Route::get('/posts', [AdminController::class, 'posts']);
    Route::get('/posts/{id}', [AdminController::class, 'showPost']);
    Route::post('/posts', [AdminController::class, 'storePost']);
    Route::put('/posts/{id}', [AdminController::class, 'updatePost']);
    Route::delete('/posts/{id}', [AdminController::class, 'deletePost']);
    
    // مدیریت دروس
    Route::get('/lessons', [AdminController::class, 'lessons']);
    Route::post('/lessons', [AdminController::class, 'storeLesson']);
    Route::put('/lessons/{id}', [AdminController::class, 'updateLesson']);
    Route::delete('/lessons/{id}', [AdminController::class, 'deleteLesson']);
    Route::post('/upload/image', [AdminController::class, 'uploadImage']);
});

// ========== مسیر کمکی اجرای مایگریشن و پاکسازی کش هاست ==========
Route::get('/migrate-db-update', function (\Illuminate\Http\Request $request) {
    if ($request->get('key') !== 'noojavaneh_update_2026') {
        return response()->json(['error' => 'Unauthorized key'], 403);
    }

    try {
        // اجرای مایگریشن‌های جدید (بدون دست زدن به اطلاعات قبلی)
        \Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Artisan::output();

        // افزودن ایمن ستون‌های تکمیلی به جدول profiles در صورت وجود جدول
        if (\Illuminate\Support\Facades\Schema::hasTable('profiles')) {
            \Illuminate\Support\Facades\Schema::table('profiles', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_age')) $table->integer('mother_age')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_education')) $table->string('mother_education')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'father_education')) $table->string('father_education')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_employment_status')) $table->string('mother_employment_status')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'family_income_status')) $table->string('family_income_status')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'number_of_teenagers')) $table->integer('number_of_teenagers')->default(0);
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_gender')) $table->string('teenager_gender')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_age_range')) $table->string('teenager_age_range')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'has_attended_parenting_course')) $table->boolean('has_attended_parenting_course')->default(false);
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_phone_internet_usage')) $table->string('teenager_phone_internet_usage')->nullable();
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_living_with')) $table->string('teenager_living_with')->nullable();
            });
        }

        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('cache:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'دیتابیس بدون تغییر داده‌های قبلی با موفقیت به‌روزرسانی و کش لاراول پاک شد.',
            'migrate_output' => $migrateOutput,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// ========== مسیرهای تست (فقط برای توسعه) ==========
if (app()->environment('local')) {
    Route::get('/test-route', function () {
        return response()->json(['message' => 'API is working']);
    });
    
    // ✅ مسیر تست برای چک کردن پیشرفت
    Route::get('/test-progress/{userId}', function ($userId) {
        $progress = App\Models\UserProgress::where('user_id', $userId)->get();
        return response()->json($progress);
    });
}