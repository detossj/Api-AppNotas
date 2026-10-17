<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transforma la categoría al formato camelCase que espera React Native.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'icon'       => $this->icon ?? 'book',
            'color'      => $this->color ?? '#8b5cf6',
            'notesCount' => (int) ($this->notes_count ?? ($this->relationLoaded('notes') ? $this->notes->count() : $this->notes()->count())),
            'createdAt'  => $this->created_at?->toIso8601String(),
            'updatedAt'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
