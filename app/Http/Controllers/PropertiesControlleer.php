<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Ehann\RediSearch\Fields\Tag;
use Illuminate\Support\Facades\Redis;
use Faker\Factory as Faker;
use MacFJA\RediSearch\Query\Builder\AndGroup;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\OrGroup;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\Query\Builder\Word;


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


    public function Searchtest()
    {

        $awal = microtime(true);
            $clientFacade = new ClientFacade();
            $client = $clientFacade->getClient(Redis::client());
            $queryBuilder = new \MacFJA\RediSearch\Query\Builder();
            $query = $queryBuilder
                ->addElement(new Word('Commodi'))
                // ->addElement(new TagFacet(['furnish'],'unfurnished'))
                // //->addElement(new NumericFacet('price', 8774349532,43505326986))
                // ->addElement(new TagFacet(['condition'],'new'))
                // ->addElement(new TagFacet(['location'],'Malang'))
                // ->addElement(new TagFacet(['certificate'],'SHM'))
                //->addElement(new OrGroup([new Word('furnished'),new Word('furnished')]))
            ->render();
            $search = new \MacFJA\RediSearch\Redis\Command\Search();
            $search
                ->setIndex('properties-idx')
                //->setHighlight(['furnish'])
                ->setSortBy('price')
                ->setLimit(1,6829)
                ->setQuery($query);
                
            $results = $client->execute($search);
            $items=$results->current();
            $total=$results->getTotalCount();
            echo '<pre>';print_r($total);
            foreach ($items as $key => $value) {
                echo '<pre>';print_r($value->getFields());
            }
            $akhir = microtime(true);
            $lama = ($akhir - $awal) ;
            echo "Lama eksekusi script adalah: ".$lama." microsecond";
    }

    
}
