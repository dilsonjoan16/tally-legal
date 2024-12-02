<?php

namespace App\Http\Controllers;

use App\Services\SaveRemoteAssets;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Constructor for the Controller class.
     *
     * @param \App\Services\SaveRemoteAssets $saveRemoteAssets An instance of the SaveRemoteAssets service.
     */
    public function __construct(public SaveRemoteAssets $saveRemoteAssets)
    {
    }

    /**
     * Retrieves a JSON response of all non-trashed records of the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to retrieve records from.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of records.
     */
    protected function abstractIndex(Model $model): JsonResponse
    {
        return response()->json([
            $model->getTable() => $model::withoutTrashed()->get()
        ], 200);
    }

    /**
     * Retrieves a JSON response of the given model instance and its relationships.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to retrieve.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the model instance and its relationships.
     */
    protected function abstractShow(Model $model): JsonResponse
    {
        $relations = $model->getRelations();

        return response()->json([
            $model->getTable() => $model->loadMissing($relations)
        ], 200);
    }

    /**
     * Retrieves a JSON response of all trashed records of the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to retrieve trashed records from.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of trashed records.
     */
    protected function abstractGetTrashed(Model $model): JsonResponse
    {
        return response()->json([
            $model->getTable() => $model::onlyTrashed()->get()
        ], 200);
    }

    /**
     * Retrieves a JSON response of all records, including trashed records, of the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to retrieve records from.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of records.
     */
    protected function abstractGetAll(Model $model): JsonResponse
    {
        return response()->json([
            $model->getTable() => $model::withTrashed()->get()
        ], 200);
    }

    /**
     * Retrieves a JSON response of all records, including trashed records, of the given model
     * and its relationships.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to retrieve records from.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of records.
     */
    protected function abstractGetAllRegistersWithDetails(Model $model): JsonResponse
    {
        $relations = $model->getRelations();

        return response()->json([
            $model->getTable() => $model::withTrashed()->with($relations)->get()
        ], 200);
    }

    /**
     * Deletes a record from the database.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    protected function abstractDelete(Model $model): JsonResponse
    {
        try {
            $model->delete();
            return response()->json([
                'message' => class_basename($model::class) . ' Deleted'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error deleting ' . class_basename($model::class), ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently deletes a record from the database.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to permanently delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    protected function abstractForceDelete(Model $model): JsonResponse
    {
        try {
            $model->forceDelete();
            return response()->json([
                'message' => class_basename($model::class) . ' Force Deleted'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error force deleting ' . class_basename($model::class), ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Restores a record from the database.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to restore.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    protected function abstractRestore(Model $model): JsonResponse
    {
        try {
            $model->restore();
            return response()->json([
                'message' => class_basename($model::class) . ' Restored'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error restoring ' . class_basename($model::class), ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Restores all trashed records of the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model instance to restore records from.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    protected function abstractRestoreAll(Model $model): JsonResponse
    {
        try {
            $model::onlyTrashed()->restore();
            return response()->json([
                'message' => class_basename($model::class) . ' Restored'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error restoring ' . class_basename($model::class), ['error' => $th]);
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
