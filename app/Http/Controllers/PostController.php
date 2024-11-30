<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Enums\StatusEnum;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function __construct(public Post $model)
    {
        parent::__construct();
        $this->model = new Post();
    }

    public function index() {
        return $this->abstractIndex($this->model);
    }

    public function getAllPosts() {
        return $this->abstractGetAll($this->model);
    }

    public function getTrashedPosts() {
        return $this->abstractGetTrashed($this->model);
    }

    public function getAllPostsWithDetails() {
        return $this->abstractGetAllRegistersWithDetails($this->model);
    }

    public function getPostsByUser(User $user) {
        return response()->json([
            'posts' => Post::withoutTrashed()->where('user_id', $user->id)->with(['category', 'user'])->get()
        ]);
    }

    public function getPostsByCategory(PostCategory $category) {
        return response()->json([
            'posts' => Post::withoutTrashed()->where('category_id', $category->id)->with(['category', 'user'])->get()
        ]);
    }

    public function getPostDetails(Post $post) {
        return $this->abstractShow($post);
    }

    public function storePost(Request $request) {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category_id' => 'required|integer'
        ]);

        try {
            $request->merge([
                'user_id' => auth()->user()->id
            ]);

            Post::create($request->all());
        } catch (\Throwable $th) {
            Log::error('Error creating post', ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Post created',
        ], 201);
    }

    public function updatePost(Post $post, Request $request) {
        $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'status' => 'nullable|in:'. StatusEnum::ACTIVE->value . ',' . StatusEnum::INACTIVE->value
        ]);

        try {
            $post->update($request->all());
        } catch (\Throwable $th) {
            Log::error('Error updating post', ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Post updated',
        ], 200);
    }

    public function deletePost(Post $post) {
        return $this->abstractDelete($post);
    }

    public function restorePost(Post $post) {
        return $this->abstractRestore($post);
    }

    public function forceDeletePost(Post $post) {
        return $this->abstractForceDelete($post);
    }

    public function restoreAllPosts() {
        return $this->abstractRestoreAll($this->model);
    }

    public function manageImage(Post $post, Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $post->image = $this->saveRemoteAssets->__invoke($request, $post->image, $this->model->getTable(), 'image');
            $post->save();
        } catch (\Throwable $th) {
            Log::error('Error updating post image', ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Post image updated',
        ], 200);
    }
}
