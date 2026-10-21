<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    /**
     * Listar notas del usuario autenticado transformadas a camelCase para React Native.
     */
    public function index(Request $request)
    {
        $query = $request->user()->notes()->with('category');

        // Filtrar por categoryId o category_id si se pasa como parámetro
        $categoryId = $request->query('categoryId') ?? $request->query('category_id');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $notes = $query->orderBy('is_pinned', 'desc')
                       ->orderBy('id', 'desc')
                       ->get();

        return NoteResource::collection($notes);
    }

    /**
     * Guardar una nueva nota para el usuario autenticado.
     */
    public function store(Request $request)
    {
        // Normalizar propiedades recibidas desde React Native (camelCase a snake_case de BD)
        if ($request->has('categoryId') && !$request->has('category_id')) {
            $request->merge(['category_id' => $request->categoryId]);
        }
        if ($request->has('isPinned') && !$request->has('is_pinned')) {
            $request->merge(['is_pinned' => $request->isPinned]);
        }

        $userId = $request->user()->id;

        $validatedData = $request->validate([
            'id'          => 'nullable|string|max:36',
            'category_id' => [
                'required',
                'string',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
            'is_pinned'   => 'nullable|boolean',
            'date'        => 'nullable|string',
            'image'       => 'nullable|string',
        ]);

        if (!empty($validatedData['image'])) {
            $validatedData['image'] = $this->processImageAttribute($validatedData['image'], $userId);
        }
        if (!empty($validatedData['content'])) {
            $validatedData['content'] = $this->processContentImages($validatedData['content'], $userId);
        }

        // Si ya existe la nota con este ID (por ejemplo sincronización reintentada), la actualizamos
        if (!empty($validatedData['id'])) {
            $existing = $request->user()->notes()->find($validatedData['id']);
            if ($existing) {
                $existing->update($validatedData);
                return (new NoteResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $note = $request->user()->notes()->create($validatedData);

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mostrar una nota específica.
     */
    public function show(Request $request, string $id)
    {
        $note = $request->user()->notes()->with('category')->findOrFail($id);

        return new NoteResource($note);
    }

    /**
     * Actualizar una nota existente.
     */
    public function update(Request $request, string $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        // Normalizar propiedades recibidas desde React Native
        if ($request->has('categoryId') && !$request->has('category_id')) {
            $request->merge(['category_id' => $request->categoryId]);
        }
        if ($request->has('isPinned') && !$request->has('is_pinned')) {
            $request->merge(['is_pinned' => $request->isPinned]);
        }

        $userId = $request->user()->id;

        $validatedData = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                'string',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
            'is_pinned'   => 'nullable|boolean',
            'date'        => 'nullable|string',
            'image'       => 'nullable|string',
        ]);

        if (array_key_exists('image', $validatedData) && !empty($validatedData['image'])) {
            $validatedData['image'] = $this->processImageAttribute($validatedData['image'], $userId);
        }
        if (array_key_exists('content', $validatedData) && !empty($validatedData['content'])) {
            $validatedData['content'] = $this->processContentImages($validatedData['content'], $userId);
        }

        $note->update($validatedData);

        return new NoteResource($note);
    }

    /**
     * Eliminar una nota.
     */
    public function destroy(Request $request, string $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        $note->delete();

        return response()->json([
            'message' => 'Nota eliminada exitosamente'
        ], 200);
    }

    /**
     * Procesa y guarda imágenes base64 si son enviadas en el campo image de la nota.
     */
    private function processImageAttribute(?string $image, int|string $userId): ?string
    {
        if (!$image) {
            return null;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $data = substr($image, strpos($image, ',') + 1);
            $ext = strtolower($type[1]);
            $extension = $ext === 'jpeg' ? 'jpg' : $ext;
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $filename = Str::uuid() . '.' . $extension;
                Storage::disk('local')->put("notes/{$userId}/" . $filename, $decoded);
                return 'api/images/' . $filename;
            }
        }

        return $image;
    }

    /**
     * Procesa y extrae imágenes base64 incrustadas dentro del contenido de la nota.
     */
    private function processContentImages(?string $content, int|string $userId): ?string
    {
        if (!$content) {
            return $content;
        }

        return preg_replace_callback('/<!--image:(data:image\/(\w+);base64,[^>]+)-->/', function ($matches) use ($userId) {
            $raw = $matches[1];
            $type = $matches[2];
            $data = substr($raw, strpos($raw, ',') + 1);
            $ext = strtolower($type);
            $extension = $ext === 'jpeg' ? 'jpg' : $ext;
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $filename = Str::uuid() . '.' . $extension;
                Storage::disk('local')->put("notes/{$userId}/" . $filename, $decoded);
                return '<!--image:api/images/' . $filename . '-->';
            }
            return $matches[0];
        }, $content);
    }
}
