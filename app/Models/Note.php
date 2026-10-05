<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    // Solo permito que se guarden estos datos de forma automática
    protected $fillable = ['title', 'content', 'category_id', 'is_pinned'];

    // Una nota le pertenece estrictamente a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
