<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Category;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        // گرفتن ID دسته‌بندی‌ها
        $mindsetCategory = Category::where('slug', 'mindset-mastery')->first();
        
        if ($mindsetCategory) {
            $lessons = [
                [
                    'category_id' => $mindsetCategory->id,
                    'title' => 'مقدمه: چرا ذهنیت مهم است؟',
                    'description' => 'آشنایی با اهمیت ذهنیت در تربیت نوجوان',
                    'content_text' => 'متن کامل درس...',
                    'video_url' => '/videos/ch1/intro.mp4',
                    'video_type' => 'local',
                    'order_index' => 1,
                    'estimated_time' => 15,
                    'is_published' => true,
                ],
                [
                    'category_id' => $mindsetCategory->id,
                    'title' => 'شناسایی باورهای محدودکننده',
                    'description' => 'چگونه باورهای خود را شناسایی کنیم',
                    'content_text' => 'متن کامل درس...',
                    'podcast_url' => '/podcasts/ch1/ep1.mp3',
                    'order_index' => 2,
                    'estimated_time' => 20,
                    'prerequisite_id' => null,
                    'is_published' => true,
                ],
                // می‌تونی درس‌های بیشتری اضافه کنی
            ];

            foreach ($lessons as $lesson) {
                Lesson::create($lesson);
            }
        }
    }
}