<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Note extends Model
{
    use HasUuids;

    // Solo permito que se guarden estos datos de forma automática
    protected $fillable = ['id', 'title', 'content', 'category_id', 'is_pinned', 'image', 'date', 'tags'];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    // Una nota le pertenece estrictamente a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
