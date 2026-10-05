<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Solo permito que se guarden estos datos de forma automática:
    protected $fillable = ['name', 'user_id'];

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
