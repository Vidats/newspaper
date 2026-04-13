<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Menu;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        $title = $this->faker->unique()->sentence(6); // Tạo tiêu đề 6 từ

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . time(),
            'summary' => $this->faker->paragraph(2),
            'content' => $this->faker->paragraphs(5, true), // Tạo bài viết dài 5 đoạn

            // Tự động bốc ngẫu nhiên 1 ID đã có trong Database để gán vào Khóa ngoại
            'user_id' => User::inRandomOrder()->first()->id,
            'menu_id' => Menu::inRandomOrder()->first()->id,
            'media_id' => Media::inRandomOrder()->first()->id,
        ];
    }
}
