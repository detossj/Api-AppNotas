<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Listar todas las categorías del usuario autenticado traducidas a camelCase.
     */
    public function index(Request $request)
    {
        $categories = $request->user()
            ->categories()
            ->withCount('notes')
            ->latest()
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Crear una nueva categoría para el usuario autenticado.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id'    => 'nullable|string|max:36',
            'name'  => 'required|string|max:255',
            'icon'  => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        $category = $request->user()->categories()->create($validatedData);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mostrar una categoría específica con su contador de notas.
     */
    public function show(Request $request, string $id)
    {
        $category = $request->user()
            ->categories()
            ->withCount('notes')
            ->findOrFail($id);

        return new CategoryResource($category);
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(Request $request, string $id)
    {
        $category = $request->user()->categories()->findOrFail($id);

        $validatedData = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'icon'  => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        $category->update($validatedData);

        return new CategoryResource($category);
    }

    /**
     * Eliminar una categoría y sus notas asociadas.
     */
    public function destroy(Request $request, string $id)
    {
        $category = $request->user()->categories()->findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente'
        ], 200);
    }
}
