<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // دریافت نظرات یک پست
    public function index($postId)
    {
        $comments = Comment::with('user')
            ->where('post_id', $postId)
            ->where('is_approved', true)  
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json($comments);
    }


    public function store(Request $request)
    {
        \Log::info('Comment request:', $request->all());
        
        $request->validate([
            'post_id' => 'required|exists:blog_posts,id',
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
    
        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
            'parent_id' => $request->parent_id,
            'is_approved' => false,
        ]);
    
        return response()->json($comment, 201);
    }

    // به‌روزرسانی نظر (فقط نویسنده)
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'عدم دسترسی'], 403);
        }
        
        $request->validate([
            'content' => 'required|string|min:3',
        ]);
        
        $comment->update(['content' => $request->input('content')]);  // ✅ تغییر مهم
        
        return response()->json($comment);
    }

    // حذف نظر (فقط نویسنده یا ادمین)
    public function destroy($id, Request $request)
    {
        $comment = Comment::findOrFail($id);
        
        if ($comment->user_id !== $request->user()->id && $request->user()->profile?->role !== 'admin') {
            return response()->json(['message' => 'عدم دسترسی'], 403);
        }
        
        $comment->delete();
        
        return response()->json(['message' => 'نظر حذف شد']);
    }
}