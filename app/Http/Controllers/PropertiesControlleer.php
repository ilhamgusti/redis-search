<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Ehann\RediSearch\Fields\NumericField;
use Ehann\RediSearch\Fields\Tag;
use Illuminate\Support\Facades\Redis;
use Faker\Factory as Faker;
use MacFJA\RediSearch\Query\Builder\AndGroup;
use MacFJA\RediSearch\Query\Builder\Negation;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\Optional;
use MacFJA\RediSearch\Query\Builder\OrGroup;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\Query\Builder\Word;

use function PHPUnit\Framework\greaterThan;

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
            ->addElement(new TagFacet(['location'], 'Semarang'))
            ->render();
        $search = new \MacFJA\RediSearch\Redis\Command\Search();
        $search
            ->setIndex('properties-idx')
            ->setLimit(0, 100)
            ->setQuery($query);

        $results = $client->execute($search);
        $items = $results->current();
        $total = $results->getTotalCount();
        echo '<pre>';
        print_r($total);
        foreach ($items as $key => $value) {
            echo '<pre>';
            print_r($value->getFields());
        }
        $akhir = microtime(true);
        $lama = ($akhir - $awal);
        echo "Lama eksekusi script adalah: " . $lama . " microsecond";
    }


    public function Searchbyid(int $id)
    {
        $keys = 'properties:detail:' . $id;
        $data = Redis::hgetall($keys);
        $clientFacade = new ClientFacade();
        $client = $clientFacade->getClient(Redis::client());
        $queryBuilder = new \MacFJA\RediSearch\Query\Builder();
        $query = $queryBuilder
            ->addElement(new Word('porro eligendi'))
            // ->addElement(NumericFacet::greaterThanOrEquals('price',$data['price']))
            ->addElement(new TagFacet(['location'], $data['location']))
            //->addElement(new TagFacet(['type'],$data['type']))
            ->addElement(
                new TagFacet(['type'], $data['type'])
            )
            ->addElement(new Negation(
                new TagFacet(['id'], $data['id'])
            ))
            ->render();
        $search = new \MacFJA\RediSearch\Redis\Command\Search();
        $search
            ->setIndex('properties-idx')
            ->setLimit(0, 100)
            ->setQuery($query);
        $results = $client->execute($search);
        $items = $results->current();
        $arr = [];
        $total = $results->getTotalCount();
        echo '<pre>';
        print_r($total);
        foreach ($items as $key => $value) {
            array_push($arr, $value->getFields());
            //echo '<pre>';print_r($value->getFields());
        }
        $terkait = $arr;
        dump(["Get Data berdasarkan id" => $data, 'pencarian terkait' => $terkait]);
    }


    public function Searchdeveloper()
    {
        $clientFacade = new ClientFacade();
        $search2 = new \MacFJA\RediSearch\Redis\Command\Search();
        $client = $clientFacade->getClient(Redis::client());
        $queryBuilder = new \MacFJA\RediSearch\Query\Builder();
        $query = $queryBuilder
            ->addElement(new Word("Mustika"))
            ->render();

        $searchproperties  = $search2->setIndex('properties-idx')
            ->setQuery($query);

        $results = $client->execute($searchproperties);
        $items = $results->current();
        
        $proerties['properties'] = [];
        $developer['developer'] = [];
        foreach ($items as $value) {
            if (isset($value->getFields()['developerid'])) {
                array_push($proerties['properties'], $value->getFields());
            } else {
                $developer['developer'] = $value->getFields();
            }
        }
        foreach ($developer as  $value) {
            if (isset($value['id'])) {
                $developer['developer']['properties'] = $this->search($proerties,'developerid',$value['id']);
            }
            
        }
        dump(
            [
                'pencarian developer' => $developer,
                'pencarian properties ' => $proerties,
            ]
        );
    }

    private function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search($subarray, $key, $value));
            }
        }

        return $results;
    }


   
    
}
