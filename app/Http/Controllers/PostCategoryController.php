<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostCategoryController extends Controller
{
    public function __construct(public PostCategory $model) {
        parent::__construct();
        $this->model = new PostCategory;
    }

    public function index() {
        return $this->abstractIndex($this->model);
    }

    public function getTrashedCategories() {
        return $this->abstractGetTrashed($this->model);
    }

    public function getAllCategories() {
        return $this->abstractGetAll($this->model);
    }

    public function getCategoryDetails(PostCategory $category) {
        return $this->abstractShow($category);
    }

    public function storeCategory(Request $request) {
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

    public function updateCategory(PostCategory $category, Request $request) {
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

    public function deleteCategory(PostCategory $category) {
        return $this->abstractDelete($category);
    }

    public function restoreCategory(PostCategory $category) {
        return $this->abstractRestore($category);
    }

    public function forceDeleteCategory(PostCategory $category) {
        return $this->abstractForceDelete($category);
    }

    public function restoreAllCategories() {
        return $this->abstractRestoreAll($this->model);
    }
}
