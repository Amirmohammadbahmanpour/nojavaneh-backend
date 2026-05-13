<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
// database/seeders/LessonsSeeder.php
public function run()
{
    $categoryId = App\Models\Category::where('slug', 'mindset-mastery')->first()->id;
    
    $lessons = [
        ['title' => 'درس اول: مقدمه', 'order_index' => 1],
        ['title' => 'درس دوم: تکنیک‌های عملی', 'order_index' => 2],
        ['title' => 'درس سوم: تمرین نهایی', 'order_index' => 3],
    ];
    
    foreach ($lessons as $lesson) {
        App\Models\Lesson::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'category_id' => $categoryId,
            'title' => $lesson['title'],
            'description' => 'توضیحات درس',
            'order_index' => $lesson['order_index'],
            'is_published' => true,
        ]);
    }
}
}
