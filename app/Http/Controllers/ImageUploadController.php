<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class ImageUploadController extends Controller
{
    /**
     * Subir una imagen (soporta multipart/form-data y base64/data-uri).
     * Solo accesible por usuarios autenticados; las imágenes se guardan en almacenamiento privado.
     */
    public function upload(Request $request)
    {
        $baseUrl = $request->schemeAndHttpHost();
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $userDir = "notes/{$user->id}";

        // 1. Manejo de archivo multipart estándar
        if ($request->hasFile('image') || $request->hasFile('file')) {
            $file = $request->file('image') ?? $request->file('file');
            
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo subido no es válido o excedió el tamaño permitido: ' . $file->getErrorMessage(),
                ], 422);
            }

            $clientExt = strtolower($file->getClientOriginalExtension());
            $guessedExt = strtolower($file->guessExtension() ?? '');
            $extension = $clientExt ?: ($guessedExt ?: 'jpg');
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $filename = Str::uuid() . '.' . $extension;
            
            // Guardar en disco local privado bajo la carpeta del usuario: storage/app/private/notes/{user_id}/{filename}
            Storage::disk('local')->putFileAs($userDir, $file, $filename);

            $apiPath = 'api/images/' . $filename;
            $fullUrl = $baseUrl . '/' . $apiPath;

            return response()->json([
                'success'  => true,
                'path'     => $apiPath,
                'url'      => $fullUrl,
                'filename' => $filename,
            ], 201);
        }

        // 2. Manejo de imagen en formato base64 o data-uri
        $rawImage = $request->input('image') ?? $request->input('image_base64');
        if ($rawImage && is_string($rawImage)) {
            $extension = 'jpg';
            $data = $rawImage;

            if (preg_match('/^data:image\/(\w+);base64,/', $rawImage, $type)) {
                $data = substr($rawImage, strpos($rawImage, ',') + 1);
                $ext = strtolower($type[1]);
                $extension = $ext === 'jpeg' ? 'jpg' : $ext;
            }

            $decoded = base64_decode($data);
            if ($decoded === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo decodificar la imagen base64.',
                ], 422);
            }

            $filename = Str::uuid() . '.' . $extension;
            
            // Guardar en disco local privado bajo la carpeta del usuario
            Storage::disk('local')->put("{$userDir}/{$filename}", $decoded);

            $apiPath = 'api/images/' . $filename;
            $fullUrl = $baseUrl . '/' . $apiPath;

            return response()->json([
                'success'  => true,
                'path'     => $apiPath,
                'url'      => $fullUrl,
                'filename' => $filename,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se proporcionó ningún archivo ni imagen en la petición.',
        ], 400);
    }

    /**
     * Endpoint protegido para servir imágenes.
     * Solo permite el acceso al usuario propietario de la imagen / nota.
     */
    public function serve(Request $request, string $filename)
    {
        // 1. Autenticación por token Bearer o parámetro query ?token=
        $token = $request->bearerToken() ?? $request->query('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado. Se requiere token para acceder a esta imagen privada.',
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido o expirado.',
            ], 401);
        }

        $user = $accessToken->tokenable;
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 401);
        }

        $cleanFilename = basename($filename);
        $localDisk = Storage::disk('local');
        $userFilePath = "notes/{$user->id}/{$cleanFilename}";

        // 2. Verificar si el archivo está en la carpeta privada del usuario
        if ($localDisk->exists($userFilePath)) {
            return response()->file($localDisk->path($userFilePath), [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            ]);
        }

        // 3. Fallback para notas creadas antes de la separación por carpetas:
        // Verificamos si este usuario es dueño de alguna nota que referencie este archivo
        $ownsNote = $user->notes()
            ->where(function ($query) use ($cleanFilename) {
                $query->where('image', 'LIKE', "%{$cleanFilename}%")
                      ->orWhere('content', 'LIKE', "%{$cleanFilename}%");
            })
            ->exists();

        if ($ownsNote) {
            // Si la imagen estaba en storage/app/public/notes, la migramos a la carpeta privada del usuario
            if (Storage::disk('public')->exists("notes/{$cleanFilename}")) {
                $content = Storage::disk('public')->get("notes/{$cleanFilename}");
                $localDisk->put($userFilePath, $content);
                Storage::disk('public')->delete("notes/{$cleanFilename}");

                return response()->file($localDisk->path($userFilePath), [
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                    'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                ]);
            }

            // Si estaba en la raíz del disco local privado
            if ($localDisk->exists("notes/{$cleanFilename}")) {
                $content = $localDisk->get("notes/{$cleanFilename}");
                $localDisk->put($userFilePath, $content);
                $localDisk->delete("notes/{$cleanFilename}");

                return response()->file($localDisk->path($userFilePath), [
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                    'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                ]);
            }
        }

        // Si no existe o pertenece a otro usuario, denegar acceso
        return response()->json([
            'success' => false,
            'message' => 'No autorizado para ver esta imagen o la imagen no existe.',
        ], 403);
    }
}
