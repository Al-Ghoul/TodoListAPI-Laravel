<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Resources\TodoCollection;
use App\Models\Todo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'todos'

], function () {

    Route::get('', function (Request $request) {
        $perPage = $request->input('limit', 15);  // default to 10 if not set

        return new TodoCollection(Todo::paginate($perPage));
    });
    Route::post('', function (Request $request) {
        $validatedData = $request->validate([
            'title' => 'required|string|unique:todos',
            'description' => 'required|string',
        ]);

        $user = $request->user();
        $todo =  $user->todos()->create($validatedData, ['user_id' => $request->user()->id]);

        return response()->json([
            'message' => 'Todo created successfully',
            'data' => $todo,
            'success' => true,
        ]);
    })->middleware('auth:api');

    Route::patch('{id}', function (Request $request, $id) {
        $validatedData = $request->validate([
            'title' => 'sometimes|string|unique:todos',
            'description' => 'sometimes|string',
        ]);
        try {
            $todo = Todo::findOrFail($id);
            if ($request->user()->id !== $todo->user_id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $todo->fill($validatedData);

            if ($todo->isDirty()) { // Only save if there are changes
                $todo->save();
            } else {
                return response()->json(['message' => 'No changes detected'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json(['message' => 'Todo updated successfully!', 'data' => $todo], 200);
    })->middleware('auth:api');

    Route::delete('{id}', function (Request $request, $id) {
        try {
            $todo = Todo::findOrFail($id);

            if ($request->user()->id !== $todo->user_id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $todo->delete();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json(['message' => 'Todo deleted successfully!'], 200);
    })->middleware('auth:api');
});
