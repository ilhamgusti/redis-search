<?php
namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use Faker\Factory as Faker;

final class RedisSearchService
{
    public $client;
    public function __construct(
        public ClientFacade $clientFacade,
    ) {
        $this->client = $clientFacade->getClient(Redis::client());
    }
    public static function make()
    {
        $clientFacade = new ClientFacade();
        return new self($clientFacade);
    }

    public function buildIndex(string $indexName, array $prefixHash)
    {
        $builder = new \MacFJA\RediSearch\IndexBuilder();
        $builder
            ->setPrefixes($prefixHash)
            ->setIndex($indexName)
            ->addTagField('id', sortable: true)
            ->addTextField('title')
            ->addTextField('address')
            ->addTagField('location', separator: ',')
            ->addNumericField('price', sortable:true)
            ->addNumericField('landArea')
            ->addNumericField('buildingSize')
            ->addNumericField('bedroom')
            ->addNumericField('bathroom')
            ->addTextField('certificate')
            ->addTextField('type')
            ->addTextField('furnish')
            ->addTextField('condition')
            ->addTextField('category')
            ->addNumericField('created_at', sortable:true)
            ->create($this->client);

        return $this;
    }

    public function addDocument($indexName, $data, $hash)
    {

        $index = new \MacFJA\RediSearch\Index($indexName, $this->client);
        $index->addDocumentFromArray($data, $hash);
        return $this;
    }

    public function search(string $indexName, string $query, ?array $highlights = null, ?array $returnFields, ?int $limitOffset, ?int $limitSize)
    {

        $search = new \MacFJA\RediSearch\Redis\Command\Search();
        $search
            ->setIndex($indexName)
            ->setQuery($query)
            ->setWithScores();
            
            if ($limitOffset && $limitSize){
                $search->setLimit($limitOffset, $limitSize);
            }

            if ($highlights){
                $search->setHighlight($highlights, '<strong>', '</strong>');
            }

            if ($returnFields){
                $search->setReturn(...$returnFields);
            }
            $results = $this->client->execute($search);

        return $results;
    }


    public function example(){

    }
    public function seedingData()
    {
        $faker = Faker::create('id_ID');
        foreach (range(1,10000) as $index) {
            Property::create(
                [
                    // 'title' => $faker->words(rand(5, 20), true),
                    'title' => $faker->sentence(rand(5, 20), true),
                    'address' => $faker->address(),
                    'location' => $faker->city(),
                    'price' => $faker->numberBetween(100_000_000, 1_000_000_000_000),
                    'landArea' => $faker->numberBetween(24, 300),
                    'buildingSize'=> $faker->numberBetween(24, 300),
                    'bedroom' => $faker->numberBetween(1,10),
                    'bathroom' => $faker->numberBetween(1,4),
                    'certificate' => $faker->randomElement(['SHM', 'AJB', 'HGB','Girik','SHSRS']),
                    'type' => $faker->randomElement(['rumah', 'apartemen', 'tanah', 'ruko', 'pabrik', 'perkantoran', 'ruang usaha', 'gudang']),
                    'furnish' => $faker->randomElement(['furnished', 'unfurnished', 'semifurnished']),
                    'condition' => $faker->randomElement(['second', 'new']),
                    'created_at' => $faker->dateTime(),
                    'category' => $faker->randomElement(['special'])
                ]
            );
        }
        
    }
}