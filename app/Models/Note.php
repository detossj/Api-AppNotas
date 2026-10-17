<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Note extends Model
{
    use HasUuids;

    protected $keyType = 'string';  // Para que el id sea string y no integer
    public $incrementing = false;   // Para que el id no sea autoincremental

    protected $fillable = ['id', 'user_id', 'category_id', 'title', 'content', 'is_pinned', 'image', 'date', 'tags'];

    protected $casts = [
        'is_pinned' => 'boolean',
        'tags' => 'array',
    ];

    // Una nota le pertenece al usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Una nota le pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
