<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'title' => 'آزمون تعیین سطح و شروع صفر',
                'slug' => 'placement-test',
                'order_index' => 1,
                'image_url' => '/images/exam-start.png',
            ],
            [
                'title' => 'فصل اول: هدایت برنده',
                'slug' => 'mindset-mastery',
                'order_index' => 2,
                'image_url' => '/images/ch1.png',
            ],
            [
                'title' => 'فصل دوم: مهندسی عادات',
                'slug' => 'habit-engineering',
                'order_index' => 3,
                'image_url' => '/images/ch2.png',
            ],
            [
                'title' => 'فصل سوم: مدیریت تمرکز',
                'slug' => 'focus-management',
                'order_index' => 4,
                'image_url' => '/images/ch3.png',
            ],
            [
                'title' => 'فصل چهارم: هوش هیجانی و روابط',
                'slug' => 'emotional-intelligence',
                'order_index' => 5,
                'image_url' => '/images/ch4.png',
            ],
            [
                'title' => 'فصل پنجم: ثبات و استمرار',
                'slug' => 'consistency-mastery',
                'order_index' => 6,
                'image_url' => '/images/ch5.png',
            ],
            [
                'title' => 'آزمون نهایی و سنجش تحول',
                'slug' => 'final-test',
                'order_index' => 7,
                'image_url' => '/images/exam-end.png',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}