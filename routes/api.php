<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api'], function () {
    // Unprotected routes.
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');
    });

    // Protected routes.
    Route::group(['middleware' => 'auth.jwt'], function () {
        // Auth routes.
        Route::group(['prefix' => 'auth'], function () {
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('me', [AuthController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        });

        // User routes.
        Route::group(['prefix' => 'users'], function () {
            // Admin routes.
            Route::group(['middleware' => 'auth.admin'], function () {
                Route::get('show/{user}', [UserController::class, 'show'])->name('users.show');
                Route::get('trashed', [UserController::class, 'getTrashedUsers'])->name('users.trashed');
                Route::get('all', [UserController::class, 'getAllUsers'])->name('users.all');
                Route::get('details/{user}', [UserController::class, 'getAllUserDetails'])->name('users.details');
                Route::post('store', [UserController::class, 'store'])->name('users.store');
                Route::put('status/{user}', [UserController::class, 'updateStatus'])->name('users.status');
                Route::put('role/{user}', [UserController::class, 'updateRole'])->name('users.role');
                Route::delete('restore/{user}', [UserController::class, 'restoreUser'])->name('users.restore');
                Route::delete('restore-all', [UserController::class, 'restoreAllUsers'])->name('users.restore-all');
                Route::delete('force-delete/{user}', [UserController::class, 'forceDelete'])->name('users.force-delete');
                Route::delete('delete/{user}', [UserController::class, 'delete'])->name('users.delete');
            });

            // User routes.
            Route::get('index', [UserController::class, 'index'])->name('users.index');
            Route::put('update/{user}', [UserController::class, 'update'])->name('users.update');
        });

        // Profile routes.
        Route::group(['prefix' => 'profile'], function () {
            Route::get('show', [ProfileController::class, 'showProfile'])->name('profile.show');
            Route::post('store', [ProfileController::class, 'storeProfile'])->name('profile.store');
            Route::post('update', [ProfileController::class, 'updateProfile'])->name('profile.update');
            Route::post('upload-avatar', [ProfileController::class, 'manageAvatar'])->name('profile.avatar');
        });

        // Post routes.
        Route::group(['prefix' => 'posts'], function () {
            // Admin routes.
            Route::group(['middleware' => 'auth.admin'], function () {
                Route::get('all', [PostController::class, 'getAllPosts'])->name('posts.all');
                Route::get('trashed', [PostController::class, 'getTrashedPosts'])->name('posts.trashed');
                Route::get('by-user/{user}', [PostController::class, 'getPostsByUser'])->name('posts.by-user');
                Route::get('by-category/{category}', [PostController::class, 'getPostsByCategory'])->name('posts.by-category');
                Route::delete('restore/{post}', [PostController::class, 'restorePost'])->name('posts.restore');
                Route::delete('restore-all', [PostController::class, 'restoreAllPosts'])->name('posts.restore-all');
                Route::delete('force-delete/{post}', [PostController::class, 'forceDeletePost'])->name('posts.force-delete');
            });

            // User routes.
            Route::get('index', [PostController::class, 'index'])->name('posts.index');
            Route::get('details/{post}', [PostController::class, 'getPostDetails'])->name('posts.details');
            Route::post('store', [PostController::class, 'storePost'])->name('posts.store');
            Route::post('upload-image/{post}', [PostController::class, 'manageImage'])->name('posts.image');
            Route::put('update/{post}', [PostController::class, 'updatePost'])->name('posts.update');
            Route::delete('delete/{post}', [PostController::class, 'deletePost'])->name('posts.delete');
        });

        // Post category routes.
        Route::group(['prefix' => 'categories'], function () {
            // Admin routes.
            Route::group(['middleware' => 'auth.admin'], function () {
                Route::get('trashed', [PostCategoryController::class, 'getTrashedCategories'])->name('categories.trashed');
                Route::get('all', [PostCategoryController::class, 'getAllCategories'])->name('categories.all');
                Route::post('store', [PostCategoryController::class, 'storeCategory'])->name('categories.store');
                Route::put('update/{category}', [PostCategoryController::class, 'updateCategory'])->name('categories.update');
                Route::delete('delete/{category}', [PostCategoryController::class, 'deleteCategory'])->name('categories.delete');
                Route::delete('restore/{category}', [PostCategoryController::class, 'restoreCategory'])->name('categories.restore');
                Route::delete('restore-all', [PostCategoryController::class, 'restoreAllCategories'])->name('categories.restore-all');
                Route::delete('force-delete/{category}', [PostCategoryController::class, 'forceDeleteCategory'])->name('categories.force-delete');
            });

            // User routes.
            Route::get('index', [PostCategoryController::class, 'index'])->name('categories.index');
            Route::get('details/{category}', [PostCategoryController::class, 'getCategoryDetails'])->name('categories.details');
        });
    });
});
