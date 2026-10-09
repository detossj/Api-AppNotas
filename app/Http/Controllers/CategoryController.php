<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id' 
        ]);

        $category = Category::create($validatedData);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'data' => $category
        ], 201);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        // Validar si el usuario actual es dueño de esta categoría
        if ($category->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente'
        ], 200);
    }
}
