<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;

    // Solo permito que se guarden estos datos de forma automática:
    protected $fillable = ['id', 'name', 'user_id', 'icon', 'color'];

    // Una categoría le pertenece a un solo usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Una categoría tiene muchas notas
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
