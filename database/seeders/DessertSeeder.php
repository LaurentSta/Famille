<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class DessertSeeder extends Seeder
{
    /**
     * Desserts extraits des suggestions déjà présentes dans la banque de plats
     * (colonne "Dessert" de banque de menu staelens.xlsx). Chaque dessert est
     * relié à un ingrédient du même nom pour apparaître dans la liste de courses.
     */
    private const DESSERTS = [
        'Yaourt nature' => 'Frais (laitages, œufs, fromage)',
        'fruits frais' => 'Fruits & légumes',
        'gateau' => 'Épicerie (sec, conserves, pain)',
        'Yaourt soja' => 'Frais (laitages, œufs, fromage)',
        'crème dessert' => 'Frais (laitages, œufs, fromage)',
        'Yaourt fruit' => 'Frais (laitages, œufs, fromage)',
        'crème aux œufs' => 'Frais (laitages, œufs, fromage)',
    ];

    public function run(): void
    {
        foreach (self::DESSERTS as $name => $category) {
            $dish = Dish::updateOrCreate(
                ['name' => $name],
                ['type' => 'Dessert'],
            );

            $ingredient = Ingredient::updateOrCreate(
                ['name' => $name],
                ['category' => $category],
            );

            $dish->ingredients()->syncWithoutDetaching([$ingredient->id]);
        }

        $this->command->info(count(self::DESSERTS).' desserts importés (avec ingrédients).');
    }
}
