<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\PostCategory;
use App\Services\SaveRemoteAssets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PostCategoryController extends Controller
{
    /**
     * Constructor for the PostCategoryController class.
     *
     * @param \App\Models\PostCategory $model The Eloquent model instance to use.
     * @param \App\Services\SaveRemoteAssets $saveRemoteAssets The service for saving remote assets.
     */
    public function __construct(
        public PostCategory $model,
        public SaveRemoteAssets $saveRemoteAssets
    )
    {
        parent::__construct($saveRemoteAssets);
        $this->model = new PostCategory;
    }

    /**
     * Retrieves a JSON response of all non-trashed post categories.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of post categories.
     */
    public function index(): JsonResponse
    {
        return $this->abstractIndex($this->model);
    }

    /**
     * Retrieves a JSON response of all trashed post categories.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of trashed post categories.
     */
    public function getTrashedCategories(): JsonResponse
    {
        return $this->abstractGetTrashed($this->model);
    }

    /**
     * Retrieves a JSON response of all post categories, including trashed ones.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of all post categories.
     */
    public function getAllCategories(): JsonResponse
    {
        return $this->abstractGetAll($this->model);
    }

    /**
     * Retrieves a JSON response of the given post category and its relationships.
     *
     * @param \App\Models\PostCategory $category The Eloquent model instance to retrieve.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the post category and its relationships.
     */
    public function getCategoryDetails(PostCategory $category): JsonResponse
    {
        return $this->abstractShow($category);
    }

    /**
     * Stores a new post category in the database.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the category data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        try {
            PostCategory::create($request->all());
        } catch (\Throwable $th) {
            Log::error('Error creating category', ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Category created',
        ], 200);
    }

    /**
     * Updates an existing post category in the database.
     *
     * @param \App\Models\PostCategory $category The Eloquent model instance to update.
     * @param \Illuminate\Http\Request $request The HTTP request containing the category data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function updateCategory(PostCategory $category, Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string',
            'status' => 'nullable|in:'. StatusEnum::ACTIVE->value . ',' . StatusEnum::INACTIVE->value
        ]);

        try {
            $category->update($request->all());
        } catch (\Throwable $th) {
            Log::error('Error updating category', ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Category updated',
        ], 200);
    }

    /**
     * Deletes a post category from the database.
     *
     * @param \App\Models\PostCategory $category The Eloquent model instance to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function deleteCategory(PostCategory $category): JsonResponse
    {
        return $this->abstractDelete($category);
    }

    /**
     * Restores a post category from the database.
     *
     * @param \App\Models\PostCategory $category The Eloquent model instance to restore.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function restoreCategory(PostCategory $category): JsonResponse
    {
        return $this->abstractRestore($category);
    }

    /**
     * Permanently deletes a post category from the database.
     *
     * @param \App\Models\PostCategory $category The Eloquent model instance to permanently delete.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function forceDeleteCategory(PostCategory $category): JsonResponse
    {
        return $this->abstractForceDelete($category);
    }

    /**
     * Restores all trashed post categories from the database.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function restoreAllCategories(): JsonResponse
    {
        return $this->abstractRestoreAll($this->model);
    }
}
