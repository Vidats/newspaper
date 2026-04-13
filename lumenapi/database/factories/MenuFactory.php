<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition()
    {
        return [
           // words(2) tạo ra 2 từ ngẫu nhiên (VD: "Tin tức")
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence,
        ];
    }
}
