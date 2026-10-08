<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        // Si no hay usuarios, creamos uno de prueba
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $categories = [
            ['name' => 'Trabajo', 'icon' => 'work', 'color' => '#FF5733'],
            ['name' => 'Personal', 'icon' => 'person', 'color' => '#33FF57'],
            ['name' => 'Estudio', 'icon' => 'school', 'color' => '#3357FF'],
            ['name' => 'Ideas', 'icon' => 'lightbulb', 'color' => '#F3FF33'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'id' => Str::uuid(),
                'name' => $cat['name'],
                'user_id' => $user->id,
                'icon' => $cat['icon'],
                'color' => $cat['color']
            ]);
        }
    }
}
