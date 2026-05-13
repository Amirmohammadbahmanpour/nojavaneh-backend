<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // دریافت همه پست‌های منتشر شده
    public function index()
    {
        $posts = BlogPost::with('author')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(10);
        
        return response()->json($posts);
    }

    // دریافت یک پست با نظراتش
    public function show($id)  // تغییر از $slug به $id
    {
        $post = BlogPost::with('author')
            ->where('id', $id)  // جستجو با id
            ->where('is_published', true)
            ->firstOrFail();
        
        return response()->json($post);
    }

    // دریافت پست‌های مرتبط
    public function related($id)
    {
        $post = BlogPost::findOrFail($id);
        $related = BlogPost::where('is_published', true)
            ->where('id', '!=', $id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
        
        return response()->json($related);
    }
}