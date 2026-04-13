<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        return [
          'name' => 'anh-demo-' . $this->faker->numberBetween(1, 100) . '.jpg',
            'path' => 'media/demo-image.jpg', // Đường dẫn ảo
            'type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(50000, 2000000), // Random dung lượng
        ];
    }
}
