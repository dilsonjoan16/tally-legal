<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
            ],

            [
                'name' => 'Home',
                'slug' => 'home',
            ],

            [
                'name' => 'Highlight',
                'slug' => 'highlight',
            ],

            [
                'name' => 'Hot',
                'slug' => 'hot',
            ],

            [
                'name' => 'Close friends',
                'slug' => 'close-friends',
            ],

            [
                'name' => 'Top',
                'slug' => 'top',
            ],

            [
                'name' => 'Histories',
                'slug' => 'histories',
            ],

            [
                'name' => 'Music',
                'slug' => 'music',
            ],

            [
                'name' => 'Sports',
                'slug' => 'sports',
            ],

            [
                'name' => 'Kids',
                'slug' => 'kids',
            ],
        ];

        // Create categories.
        foreach ($categories as $category) {
            PostCategory::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'status' => StatusEnum::ACTIVE->value
            ]);
        }
    }
}
