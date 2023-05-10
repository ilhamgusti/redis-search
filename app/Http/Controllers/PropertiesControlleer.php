<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Redis;
use Faker\Factory as Faker;


class PropertiesControlleer extends Controller
{
    public function index()
    {
        $this->savePropertyData();
    }

    private function savePropertyData()
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 200; $i++) {
            $propertyData = [
                'id' => $i,
                'title' => $faker->words(3, true),
                'address' => $faker->address,
                'location' => $faker->city,
                'price' => $faker->numberBetween(100000, 1000000),
                'landArea' => $faker->numberBetween(100, 1000),
                'buildingSize' => $faker->numberBetween(50, 500),
                'bedroom' => $faker->numberBetween(1, 5),
                'bathroom' => $faker->numberBetween(1, 4),
                'certificate' => $faker->randomElement(['Freehold', 'Leasehold']),
                'type' => $faker->randomElement(['Vila', 'Apartemen', 'Rumah']),
                'furnish' => $faker->randomElement(['Furnished', 'Unfurnished', 'Partially Furnished']),
                'condition' => $faker->randomElement(['Baik', 'Sedang', 'Perlu Renovasi']),
                'created_at' => $faker->unixTime,
                'category' => $faker->randomElement(['Residensial', 'Komersial']),
                'description' => $faker->paragraph,
            ];
            Redis::hMSet("property:$i", $propertyData);
        }
        return "Data properti telah disimpan di Redis.";
    }
}
