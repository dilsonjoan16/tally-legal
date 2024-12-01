<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Post;
use App\Models\User;
use App\Models\Profile;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $categories = PostCategory::factory(10)->create();

        $users = User::factory(10)->create()->each(function ($user) {
            $user->profile()->save(Profile::factory()->make());
        });

        Post::factory(20)->create()->each(function ($post) use ($users, $categories) {
            $post->user()->associate($users->random());
            $post->category()->associate($categories->random());
            $post->save();
        });

        $postsToDelete = Post::inRandomOrder()->limit(5)->get();
        foreach ($postsToDelete as $post) {
            $post->delete();
        }

        $usersToDelete = User::inRandomOrder()->limit(5)->get();
        foreach ($usersToDelete as $user) {
            $user->delete();
        }
    }
}
