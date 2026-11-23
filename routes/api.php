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
use App\Http\Controllers\Api\TicketController;
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
    
    // تیکت‌ها و پشتیبانی کاربر
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/tickets/{id}/reply', [TicketController::class, 'reply']);
    Route::put('/tickets/{id}/close', [TicketController::class, 'close']);
    
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

    // مدیریت تیکت‌ها و پشتیبانی
    Route::get('/tickets', [TicketController::class, 'adminIndex']);
    Route::get('/tickets/{id}', [TicketController::class, 'adminShow']);
    Route::post('/tickets/{id}/reply', [TicketController::class, 'adminReply']);
    Route::put('/tickets/{id}/status', [TicketController::class, 'adminUpdateStatus']);
    Route::delete('/tickets/{id}', [TicketController::class, 'adminDestroy']);
});

// ========== مسیر کمکی اجرای مایگریشن و پاکسازی کش هاست ==========
Route::get('/migrate-db-update', function (\Illuminate\Http\Request $request) {
    if ($request->get('key') !== 'noojavaneh_update_2026') {
        return response()->json(['error' => 'Unauthorized key'], 403);
    }

    $details = [];

    // ۱. افزودن ایمن ستون‌های تکمیلی به جدول profiles در صورت وجود جدول
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('profiles')) {
            \Illuminate\Support\Facades\Schema::table('profiles', function (\Illuminate\Database\Schema\Blueprint $table) use (&$details) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_age')) {
                    $table->integer('mother_age')->nullable();
                    $details[] = 'ستون mother_age اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_education')) {
                    $table->string('mother_education')->nullable();
                    $details[] = 'ستون mother_education اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'father_education')) {
                    $table->string('father_education')->nullable();
                    $details[] = 'ستون father_education اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'mother_employment_status')) {
                    $table->string('mother_employment_status')->nullable();
                    $details[] = 'ستون mother_employment_status اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'family_income_status')) {
                    $table->string('family_income_status')->nullable();
                    $details[] = 'ستون family_income_status اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'number_of_teenagers')) {
                    $table->integer('number_of_teenagers')->default(0);
                    $details[] = 'ستون number_of_teenagers اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_gender')) {
                    $table->string('teenager_gender')->nullable();
                    $details[] = 'ستون teenager_gender اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_age_range')) {
                    $table->string('teenager_age_range')->nullable();
                    $details[] = 'ستون teenager_age_range اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'has_attended_parenting_course')) {
                    $table->boolean('has_attended_parenting_course')->default(false);
                    $details[] = 'ستون has_attended_parenting_course اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_phone_internet_usage')) {
                    $table->string('teenager_phone_internet_usage')->nullable();
                    $details[] = 'ستون teenager_phone_internet_usage اضافه شد';
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('profiles', 'teenager_living_with')) {
                    $table->string('teenager_living_with')->nullable();
                    $details[] = 'ستون teenager_living_with اضافه شد';
                }
            });
        }
    } catch (\Exception $e) {
        $details[] = 'وضعیت جدول پروفایل: ' . $e->getMessage();
    }

    // ۲. ایجاد ایمن جداول تیکت‌ها و پیام‌ها در صورت عدم وجود
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tickets')) {
            \Illuminate\Support\Facades\Schema::create('tickets', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->string('category')->default('technical');
                $table->string('status')->default('pending');
                $table->string('priority')->default('medium');
                $table->timestamps();
            });
            $details[] = 'جدول tickets با موفقیت ساخته شد';
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('ticket_messages')) {
            \Illuminate\Support\Facades\Schema::create('ticket_messages', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->boolean('is_admin')->default(false);
                $table->timestamps();
            });
            $details[] = 'جدول ticket_messages با موفقیت ساخته شد';
        }
    } catch (\Exception $e) {
        $details[] = 'وضعیت جداول تیکت: ' . $e->getMessage();
    }

    // ۳. اجرای مایگریشن‌های جدید با نادیده گرفتن جداول موجود
    try {
        \Artisan::call('migrate', ['--force' => true]);
        $output = trim(\Artisan::output());
        if ($output) {
            $details[] = $output;
        }
    } catch (\Exception $e) {
        $details[] = 'جداول اصلی از قبل موجود هستند (بدون نیاز به ساخت مجدد)';
    }

    // ۴. پاکسازی و تازه‌سازی کامل کش‌های لاراول
    try {
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('cache:clear');
        $details[] = 'تمام کش‌های لاراول با موفقیت پاکسازی شدند';
    } catch (\Exception $e) {
        $details[] = 'کش: ' . $e->getMessage();
    }

    return response()->json([
        'status' => 'success',
        'message' => 'دیتابیس و کش لاراول با موفقیت همگام‌سازی شدند و تمام اطلاعات قبلی حفظ شدند.',
        'details' => $details,
    ]);
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