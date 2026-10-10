<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::with('category')->latest()->get();
        return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
            'is_pinned'   => 'nullable|boolean',
            'date'        => 'nullable|string',
            'image'       => 'nullable|string',
        ]);

        $note = Note::create($validatedData);

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = Note::with('category')->findOrFail($id);
        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = Note::findOrFail($id);

        $validatedData = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
            'is_pinned'   => 'nullable|boolean',
            'date'        => 'nullable|string',
            'image'       => 'nullable|string',
        ]);

        $note->update($validatedData);

        return response()->json($note);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully'], 200);
    }
}
