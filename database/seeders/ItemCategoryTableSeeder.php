<?php

namespace Database\Seeders;

use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemCategory;
use Illuminate\Support\Str;
use App\Enums\Status;

class ItemCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public array $categories = [
        'Appetizers',
        'Flame Grill Burgers',
        'Veggie & Plant Based Burgers',
        'Sandwich from the Grill',
        'Hot Chicken Entrees',
        'Beef Entrees',
        'Seafood Entrees',
        'House Special Salads',
        'Zoop Soups',
        'Side Orders',
        'Beverages'
    ];

    public function run()
    {
//        $envService = new EnvEditor();
//        if ($envService->getValue('DEMO'))
        if (env('DEMO')) {
            foreach ($this->categories as $category) {
                $itemCategory = ItemCategory::create([
                    'name'        => $category,
                    'slug'        => Str::slug($category),
                    'description' => null,
                    'status'      => Status::ACTIVE,
                ]);
                $imagePath = public_path('/images/seeder/item-category/' . strtolower(str_replace(' ', '_', $category)) . '.png');

                if (file_exists($imagePath)) {
                    $itemCategory->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('offer', 'public_custom');
                }
            }
        }
    }
}
