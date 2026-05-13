<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // دریافت همه دسته‌بندی‌ها (فصل‌ها)
    public function index()
    {
        $categories = Category::with(['lessons' => function ($query) {
            $query->orderBy('order_index')->where('is_published', true);
        }])->orderBy('order_index')->get();
        
        return response()->json($categories);
    }

    // دریافت یک دسته‌بندی با تمام دروسش
    public function show($id)
    {
        $category = Category::with(['lessons' => function($query) {
            $query->orderBy('order_index')->where('is_published', true);
        }])->findOrFail($id);
        
        return response()->json($category);
    }

    // دریافت دروس یک دسته‌بندی خاص (مثلاً فصل ۱)
    public function lessons($id)
    {
        $lessons = Category::findOrFail($id)->lessons()
            ->orderBy('order_index')
            ->where('is_published', true)
            ->get();
        
        return response()->json($lessons);
    }
}