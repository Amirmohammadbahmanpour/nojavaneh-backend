<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostLike;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // ========== لایک پست ==========
    public function togglePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:blog_posts,id',
        ]);

        $like = PostLike::where('post_id', $request->post_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            PostLike::create([
                'post_id' => $request->post_id,
                'user_id' => $request->user()->id,
            ]);
            $liked = true;
        }

        $count = PostLike::where('post_id', $request->post_id)->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }

    public function checkPostLike(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:blog_posts,id',
        ]);

        $exists = PostLike::where('post_id', $request->post_id)
            ->where('user_id', $request->user()->id)
            ->exists();

        return response()->json(['liked' => $exists]);
    }

    // ========== لایک کامنت ==========
    public function toggleComment(Request $request)
    {
        \Log::info('Toggle comment like - request:', $request->all());
        
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
        ]);

        $like = CommentLike::where('comment_id', $request->comment_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'comment_id' => $request->comment_id,
                'user_id' => $request->user()->id,
            ]);
            $liked = true;
        }

        $count = CommentLike::where('comment_id', $request->comment_id)->count();

        \Log::info('Toggle comment like - result:', ['liked' => $liked, 'count' => $count]);

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }

    public function checkCommentLike(Request $request)
    {
        \Log::info('Check comment like - request:', $request->all());
        
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
        ]);

        $exists = CommentLike::where('comment_id', $request->comment_id)
            ->where('user_id', $request->user()->id)
            ->exists();

        \Log::info('Check comment like - result:', ['exists' => $exists]);

        return response()->json(['liked' => $exists]);
    }

    // ========== تعداد لایک‌ها ==========
    public function getPostLikesCount($id)
    {
        $count = PostLike::where('post_id', $id)->count();
        return response()->json(['count' => $count]);
    }

    public function getCommentLikesCount($id)
    {
        $count = CommentLike::where('comment_id', $id)->count();
        return response()->json(['count' => $count]);
    }
}