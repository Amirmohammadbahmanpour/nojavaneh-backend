<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // تابع کمکی برای چک کردن ادمین
    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }
        
        $profile = Profile::where('user_id', $user->id)->first();
        return $profile && $profile->role === 'admin';
    }

    // آمار داشبورد
    public function stats(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        return response()->json([
            'posts_count' => BlogPost::count(),
            'users_count' => User::count(),
            'pending_comments_count' => Comment::where('is_approved', false)->count(),
            'monthly_growth' => '+۱۵%',
        ]);
    }

    // مدیریت کاربران
    public function users(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $users = User::with('profile')->orderBy('created_at', 'desc')->get();

        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'full_name' => $user->profile?->full_name,
                'email' => $user->email,
                'role' => $user->profile?->role ?? 'user',
            ];
        });

        return response()->json($formattedUsers);
    }

    public function changeRole(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $profile = Profile::where('user_id', $id)->firstOrFail();
        $profile->update(['role' => $request->role]);

        return response()->json(['message' => 'نقش کاربر با موفقیت تغییر کرد']);
    }

    // مدیریت نظرات
    public function comments(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $comments = Comment::with(['user', 'post'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'is_approved' => (bool) $comment->is_approved,
                'created_at' => $comment->created_at?->toISOString(),
                'user' => [
                    'name' => $comment->user?->name,
                ],
                'post' => [
                    'title' => $comment->post?->title,
                ],
            ];
        });

        return response()->json($formattedComments);
    }

    public function approveComment(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return response()->json(['message' => 'نظر با موفقیت تأیید شد']);
    }

    public function deleteComment(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json(['message' => 'نظر با موفقیت حذف شد']);
    }

    // مدیریت پست‌ها
    public function posts(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $posts = BlogPost::with('author')->orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    public function showPost(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $post = BlogPost::findOrFail($id);
        return response()->json($post);
    }

    // ✅ ایجاد پست جدید (اصلاح شده)
// ✅ ایجاد پست جدید - اصلاح شده
public function storePost(Request $request)
{
    if (!$this->isAdmin($request)) {
        return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
    }

    Log::info('Store post request:', $request->all());

    // پشتیبانی از هر دو نام (content و post_content)
    $content = $request->input('post_content') ?? $request->input('content');
    
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image_url' => 'nullable|url',
    ]);
    
    if (empty($content)) {
        return response()->json(['message' => 'متن مقاله الزامی است'], 422);
    }

    $post = BlogPost::create([
        'title' => $request->title,
        'slug' => Str::slug($request->title),
        'description' => $request->description,
        'content' => $content,
        'image_url' => $request->image_url,
        'author_id' => $request->user()->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return response()->json($post, 201);
}

// ✅ ویرایش پست - اصلاح شده
public function updatePost(Request $request, string $id)
{
    if (!$this->isAdmin($request)) {
        return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
    }
    
    \Log::info('Update post request:', ['id' => $id, 'data' => $request->all()]);
    
    $post = BlogPost::findOrFail($id);
    
    // پشتیبانی از هر دو نام (content و post_content)
    $content = $request->input('post_content') ?? $request->input('content');
    
    if ($request->has('title')) {
        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
    }
    if ($request->has('description')) {
        $post->description = $request->description;
    }
    if (!empty($content)) {
        $post->content = $content;
    }
    if ($request->has('image_url')) {
        $post->image_url = $request->image_url;
    }

    $post->save();

    return response()->json($post);
}

    public function deletePost(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'پست با موفقیت حذف شد']);
    }

    // مدیریت دروس
    public function storeLesson(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'podcast_url' => 'nullable|url',
            'lesson_content' => 'nullable|string',
            'order_index' => 'integer',
        ]);

        $lesson = Lesson::create([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'podcast_url' => $request->podcast_url,
            'content_text' => $request->lesson_content,
            'order_index' => $request->order_index ?? 0,
            'is_published' => true,
        ]);

        return response()->json($lesson, 201);
    }

    public function updateLesson(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $lesson = Lesson::findOrFail($id);

        $data = $request->only([
            'title', 'category_id', 'description', 
            'video_url', 'podcast_url', 'order_index'
        ]);
        
        if ($request->has('lesson_content')) {
            $data['content_text'] = $request->lesson_content;
        }
        
        $lesson->update($data);

        return response()->json($lesson);
    }

    public function deleteLesson(Request $request, string $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $lesson = Lesson::findOrFail($id);
        $lesson->delete();

        return response()->json(['message' => 'درس با موفقیت حذف شد']);
    }
    // حذف کاربر
public function deleteUser(Request $request, string $id)
{
    if (!$this->isAdmin($request)) {
        return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
    }

    try {
        // پیدا کردن کاربر
        $user = User::findOrFail($id);
        
        // جلوگیری از حذف خود ادمین
        if ($user->id == $request->user()->id) {
            return response()->json(['message' => 'نمی‌توانید خودتان را حذف کنید'], 422);
        }
        
        // حذف پروفایل و سپس کاربر
        $user->profile()->delete();
        $user->delete();
        
        return response()->json(['message' => 'کاربر با موفقیت حذف شد']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'خطا در حذف کاربر'], 500);
    }
}
}