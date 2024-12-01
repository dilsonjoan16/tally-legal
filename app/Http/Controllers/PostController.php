<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Enums\StatusEnum;
use App\Models\PostCategory;
use App\Services\SaveRemoteAssets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Constructor for the PostController class.
     *
     * @param \App\Models\Post $model The Eloquent model instance to use.
     * @param \App\Services\SaveRemoteAssets $saveRemoteAssets The service for saving remote assets.
     */
    public function __construct(
        public Post $model,
        public SaveRemoteAssets $saveRemoteAssets
    )
    {
        parent::__construct($saveRemoteAssets);
        $this->model = new Post();
    }

    /**
     * Retrieves a JSON response of all non-trashed posts.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of posts.
     */
    public function index(): JsonResponse
    {
        return $this->abstractIndex($this->model);
    }

    /**
     * Retrieves a JSON response of all posts, including trashed ones.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of all posts.
     */
    public function getAllPosts(): JsonResponse
    {
        return $this->abstractGetAll($this->model);
    }

    /**
     * Retrieves a JSON response of all trashed posts.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of trashed posts.
     */
    public function getTrashedPosts(): JsonResponse
    {
        return $this->abstractGetTrashed($this->model);
    }

    /**
     * Retrieves a JSON response of all posts, including trashed ones, and their relationships.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of all posts.
     */
    public function getAllPostsWithDetails(): JsonResponse
    {
        return $this->abstractGetAllRegistersWithDetails($this->model);
    }

    /**
     * Retrieves a JSON response of all posts belonging to the given user.
     *
     * @param \App\Models\User $user The user instance to retrieve posts for.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of posts.
     */
    public function getPostsByUser(User $user): JsonResponse
    {
        return response()->json([
            'posts' => Post::withoutTrashed()->where('user_id', $user->id)->with(['category', 'user'])->get()
        ]);
    }

    /**
     * Retrieves a JSON response of all posts belonging to the given post category.
     *
     * @param \App\Models\PostCategory $category The post category instance to retrieve posts for.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of posts.
     */
    public function getPostsByCategory(PostCategory $category): JsonResponse
    {
        return response()->json([
            'posts' => Post::withoutTrashed()->where('category_id', $category->id)->with(['category', 'user'])->get()
        ]);
    }

    /**
     * Retrieves a JSON response of the given post and its relationships.
     *
     * @param \App\Models\Post $post The post instance to retrieve.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the post and its relationships.
     */
    public function getPostDetails(Post $post): JsonResponse
    {
        return $this->abstractShow($post);
    }

    /**
     * Stores a new post in the database.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the post data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function storePost(Request $request): JsonResponse
    {
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

    /**
     * Updates an existing post in the database.
     *
     * @param \App\Models\Post $post The Eloquent model instance to update.
     * @param \Illuminate\Http\Request $request The HTTP request containing the post data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function updatePost(Post $post, Request $request): JsonResponse
    {
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

    /**
     * Deletes a post from the database.
     *
     * @param \App\Models\Post $post The Eloquent model instance to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function deletePost(Post $post): JsonResponse
    {
        return $this->abstractDelete($post);
    }

    /**
     * Restores a post from the database.
     *
     * @param \App\Models\Post $post The Eloquent model instance to restore.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function restorePost(Post $post): JsonResponse
    {
        return $this->abstractRestore($post);
    }

    /**
     * Permanently deletes a post from the database.
     *
     * @param \App\Models\Post $post The Eloquent model instance to permanently delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function forceDeletePost(Post $post): JsonResponse
    {
        return $this->abstractForceDelete($post);
    }

    /**
     * Restores all trashed posts from the database.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function restoreAllPosts(): JsonResponse
    {
        return $this->abstractRestoreAll($this->model);
    }

    /**
     * Updates the image of a post.
     *
     * @param \App\Models\Post $post The Eloquent model instance to update.
     * @param \Illuminate\Http\Request $request The HTTP request containing the image.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function manageImage(Post $post, Request $request): JsonResponse
    {
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
