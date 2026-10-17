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
        return [
            'id'         => $this->id,
            'title'      => $this->title ?? '',
            'content'    => $this->content ?? '',
            'categoryId' => $this->category_id,
            'image'      => $this->image,
            'date'       => $this->date ?? 'Hoy',
            'isPinned'   => (bool) $this->is_pinned,
            'tags'       => is_array($this->tags) ? $this->tags : ($this->tags ? json_decode($this->tags, true) : []),
            'createdAt'  => $this->created_at?->toIso8601String(),
            'updatedAt'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
