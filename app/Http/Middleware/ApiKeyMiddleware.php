<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir solicitudes preflight de CORS
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $apiKey = $request->header('X-API-KEY') ?? $request->query('api_key');
        $validKey = config('app.api_key', env('APP_API_KEY'));

        if (!$validKey || !hash_equals((string) $validKey, (string) $apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado: API Key no válida o ausente.',
            ], 401);
        }

        return $next($request);
    }
}
