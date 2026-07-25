<?php

namespace Database\Seeders;

use App\Models\Plat;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class DishBankSeeder extends Seeder
{
    /**
     * Import the family dish bank from database/data/*.csv
     * (source: banque de menu staelens.xlsx). Safe to re-run.
     */
    public function run(): void
    {
        $dataDir = database_path('data');

        foreach ($this->readCsv("$dataDir/ingredients.csv") as $row) {
            Ingredient::updateOrCreate(
                ['name' => $row['name']],
                ['category' => $row['category'] ?: null],
            );
        }

        foreach ($this->readCsv("$dataDir/dishes.csv") as $row) {
            Plat::updateOrCreate(
                ['name' => $row['name']],
                [
                    'type' => $row['type'] ?: null,
                    'low_carb' => $row['low_carb'] === '' ? null : (bool) $row['low_carb'],
                    'dessert_suggestion' => $row['dessert_suggestion'] ?: null,
                    'notes' => $row['notes'] ?: null,
                ],
            );
        }

        $ingredientIds = Ingredient::pluck('id', 'name');
        $dishIngredientIds = [];
        foreach ($this->readCsv("$dataDir/dish_ingredients.csv") as $row) {
            $dishIngredientIds[$row['dish_name']][] = $ingredientIds[$row['ingredient_name']];
        }

        $dishes = Plat::whereIn('name', array_keys($dishIngredientIds))->get()->keyBy('name');
        foreach ($dishIngredientIds as $dishName => $ids) {
            $dishes[$dishName]?->ingredients()->sync($ids);
        }

        $this->command->info(sprintf(
            '%d ingredients, %d dishes, %d dish-ingredient links imported.',
            Ingredient::count(),
            Plat::count(),
            array_sum(array_map('count', $dishIngredientIds)),
        ));
    }

    private function readCsv(string $path): \Generator
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            yield array_combine($header, $row);
        }
        fclose($handle);
    }
}
