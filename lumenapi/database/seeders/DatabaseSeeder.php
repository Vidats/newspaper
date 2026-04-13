<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Media;
use App\Models\User;
use App\Models\Post;



class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         User::factory(5)->create();
           Menu::factory(5)->create();
                   Media::factory(10)->create();
                           Post::factory(10)->create();


    }
}
