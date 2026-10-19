<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    /**
     * Transforma la nota al formato camelCase que espera React Native.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = $request->schemeAndHttpHost();

        $imageUrl = $this->image;
        if ($imageUrl) {
            $parsedPath = parse_url($imageUrl, PHP_URL_PATH) ?? $imageUrl;
            $cleanFilename = basename($parsedPath);
            if (!str_starts_with($imageUrl, 'data:') && !str_starts_with($imageUrl, 'blob:') && !str_starts_with($imageUrl, 'file:')) {
                $imageUrl = $baseUrl . '/api/images/' . $cleanFilename;
            }
        }

        $content = $this->content ?? '';
        if ($content) {
            $content = preg_replace_callback('/(<!--image:|!\[(?:image|.*?)\]\()([^)]+?)(\)|-->)/', function ($matches) use ($baseUrl) {
                $prefix = $matches[1];
                $url = trim($matches[2]);
                $suffix = $matches[3];

                if (!str_starts_with($url, 'data:') && !str_starts_with($url, 'blob:') && !str_starts_with($url, 'file:')) {
                    $parsedPath = parse_url($url, PHP_URL_PATH) ?? $url;
                    $cleanFilename = basename($parsedPath);
                    $url = $baseUrl . '/api/images/' . $cleanFilename;
                }

                return $prefix . $url . $suffix;
            }, $content);
        }

        return [
            'id'         => $this->id,
            'title'      => $this->title ?? '',
            'content'    => $content,
            'categoryId' => $this->category_id,
            'image'      => $imageUrl,
            'date'       => $this->date ?? 'Hoy',
            'isPinned'   => (bool) $this->is_pinned,
            'createdAt'  => $this->created_at?->toIso8601String(),
            'updatedAt'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
