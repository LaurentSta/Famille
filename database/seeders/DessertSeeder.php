<?php

namespace Database\Seeders;

use App\Models\Dish;
use Illuminate\Database\Seeder;

class DessertSeeder extends Seeder
{
    /**
     * Desserts extraits des suggestions déjà présentes dans la banque de plats
     * (colonne "Dessert" de banque de menu staelens.xlsx). Pas d'ingrédients :
     * ils n'apparaissent donc pas dans la liste de courses.
     */
    private const DESSERTS = [
        'Yaourt nature',
        'fruits frais',
        'gateau',
        'Yaourt soja',
        'crème dessert',
        'Yaourt fruit',
        'crème aux œufs',
    ];

    public function run(): void
    {
        foreach (self::DESSERTS as $name) {
            Dish::updateOrCreate(
                ['name' => $name],
                ['type' => 'Dessert'],
            );
        }

        $this->command->info(count(self::DESSERTS).' desserts importés.');
    }
}
