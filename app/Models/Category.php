<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;

    protected $keyType = 'string'; // Para que el id sea string y no integer
    public $incrementing = false;  // Para que el id no sea autoincremental

    protected $fillable = ['id', 'user_id', 'name', 'icon', 'color'];

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
