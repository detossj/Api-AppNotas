<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return;
        }

        $notes = [
            [
                'title' => 'Reunión de proyecto',
                'content' => 'Discutir los avances del nuevo API y definir los siguientes pasos para el frontend.',
                'is_pinned' => true,
                'image' => null,
                'date' => now()->format('Y-m-d'),
            ],
            [
                'title' => 'Comprar víveres',
                'content' => 'Leche, huevos, pan, café, frutas y verduras.',
                'is_pinned' => false,
                'image' => null,
                'date' => now()->addDays(1)->format('Y-m-d'),
            ],
            [
                'title' => 'Estudiar Laravel',
                'content' => 'Repasar Eloquent, relaciones, seeders y factories para el examen.',
                'is_pinned' => true,
                'image' => null,
                'date' => now()->format('Y-m-d'),
            ],
            [
                'title' => 'Idea de App',
                'content' => 'Crear una aplicación para gestionar notas con categorías personalizadas y soporte de markdown.',
                'is_pinned' => false,
                'image' => null,
                'date' => now()->format('Y-m-d'),
            ]
        ];

        foreach ($notes as $index => $noteData) {
            // Asignamos una categoría aleatoria o secuencial a cada nota
            $category = $categories[$index % $categories->count()];

            Note::create([
                'id' => Str::uuid(),
                'title' => $noteData['title'],
                'content' => $noteData['content'],
                'category_id' => $category->id,
                'is_pinned' => $noteData['is_pinned'],
                'image' => $noteData['image'],
                'date' => $noteData['date'],
            ]);
        }
    }
}
