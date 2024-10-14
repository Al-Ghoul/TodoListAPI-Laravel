<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use App\Http\Resources\TodoCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 15);  // default to 10 if not set
        return new TodoCollection(Todo::paginate($perPage));
    }

    public function store(Request $request)
    {
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
    }

    public function update(Request $request, $id)
    {
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
    }

    public function destroy(Request $request, $id)
    {
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
    }
}
