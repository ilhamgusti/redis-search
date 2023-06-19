<?php
namespace App\Services;

use App\Models\Developer;
use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use Faker\Factory as Faker;
use MacFJA\RediSearch\Redis\Command\Profile;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;

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

    public function locationIndex(string $indexName, array $prefixHash)
    {
        $builder = new \MacFJA\RediSearch\IndexBuilder();
        $builder
            ->setPrefixes(['location:detail:'])
            ->setIndex('locations-idx')
            ->addTagField('id', sortable: true)
            ->addTextField('name')
            ->addTagField('type')
            ->addTagField('refName', separator: ',')
            ->addTagField('refId', separator: ',')
            ->create($this->client);

        return $this;
    }

    public function addDocument($indexName, $data, $hash)
    {

        $index = new \MacFJA\RediSearch\Index($indexName, $this->client);
        $index->addDocumentFromArray($data, $hash);
        return $this;
    }

    public function aggregate(string $indexName, string $query, ?array $returnFields = null, ?int $limitOffset = 0, ?int $limitSize = 10, ?array $sortByFields = null, ?int $page = null, ?int $max = null): PaginatedResponse {

        $startTime = microtime(true); //get time in micro seconds(1 millionth)
        
        $aggregate = new \MacFJA\RediSearch\Redis\Command\Aggregate();
        $aggregate->setIndex($indexName)
        ->setQuery($query);

        if ($returnFields){
            $aggregate->setLoad(...$returnFields);
        }else{
            $aggregate->setLoadAll();
        }

        if (!is_null($limitOffset) && !is_null($limitSize)){
            $aggregate->setLimit($limitOffset, $limitSize);
        }

        if(!empty($sortByFields)){
            foreach ($sortByFields as $field => $direction) {
                $aggregate->addSortBy($field, $direction);
            }
        }

        if(!empty($max)){
            $aggregate->setSortByMax($max);
        }

        $profileResults = $this->profiling($indexName, $aggregate);

        $results = $this->client->execute($aggregate);
        $endTime = microtime(true);

        echo "AGGREGATE milliseconds to execute:". ($endTime-$startTime)*1000;

        dump("AGGREGATE ". $profileResults[1][0][0]." : " . $profileResults[1][0][1] . ' ms', $results);
        

        return $results;

    }

    public function search(string $indexName, string $query, ?array $highlights = null, ?array $returnFields = null, ?int $limitOffset = null, ?int $limitSize = null, ?array $sortByFields = null, ?string $scorer = 'TFIDF', ?int $page = null)
    {

        $perPage = 10;
        $currentPage = $page ?? 1;

        $startTime = microtime(true); //get time in micro seconds(1 millionth)
        $search = new \MacFJA\RediSearch\Redis\Command\Search();
        $search
            ->setIndex($indexName)
            ->setQuery($query)
    
            ->setWithScores();
            if (!is_null($limitOffset) && !is_null($limitSize)){
                $search->setLimit($limitOffset, $limitSize);
            }

            if ($highlights){
                $search->setHighlight($highlights);
            }

            if ($returnFields){
                $search->setReturn(...$returnFields);
            }

            $search->setScorer($scorer);

            if(!empty($sortByFields)){
                foreach ($sortByFields as $field => $direction) {
                    $search->setSortBy($field, $direction);
                }
            }
            
            $profileResults = $this->profiling($indexName, $search);

            $results = $this->client->execute($search);

            $endTime = microtime(true);

                    
            echo "SEARCH milliseconds to execute:". ($endTime-$startTime)*1000;

            dump("SEARCH ". $profileResults[1][0][0]." : " . $profileResults[1][0][1] . ' ms', $results);
            
            $datass = new LengthAwarePaginator(items: collect($results->current())->map(fn($data) => $data->getFields()), total: $results->getTotalCount(), perPage: $perPage, currentPage: $currentPage);

        return $results;
    }

    public function profiling($indexName, $search, $type = "SEARCH"){
        $command = new Profile('2.2.0');
        $command
            ->setIndex($indexName);
            if($type == 'SEARCH'){
                $command->setTypeSearch();
            }else{
                $command->setTypeAggregate();
            }
           
            $command->setQuery($search)->setLimited(); 

        $result = $this->client->execute($command);

        return $result;
    }


    public function example(){

    }
    public function seedingData($total = 1)
    {
        $faker = Faker::create('id_ID');

        // $lazyCollection1 = collect(range(1,ceil($total / 3)))->lazy();
        // $lazyCollection1->each(function (int $number) use ($faker) {
        //     Developer::create(
        //         [
        //             'title' => $faker->name(),
        //         ]
        //     );
        // });

        $lazyCollection = collect(range(1,$total))->lazy();
        $lazyCollection->each(function (int $number) use ($faker) {
            Property::create(
                [
                    'title' => $faker->sentence(rand(5, 20), true),
                    'address' => $faker->address(),
                    'location' => $faker->city(),
                    'price' => $faker->numberBetween(100_000_000, 50_000_000_000),
                    'landArea' => $faker->numberBetween(24, 300),
                    'buildingSize'=> $faker->numberBetween(24, 300),
                    'bedroom' => $faker->numberBetween(1,10),
                    'bathroom' => $faker->numberBetween(1,4),
                    'certificate' => $faker->randomElement(['SHM', 'AJB', 'HGB','Girik','SHSRS']),
                    'type' => $faker->randomElement(['rumah', 'apartemen', 'tanah', 'ruko', 'pabrik', 'perkantoran', 'ruang usaha', 'gudang']),
                    'furnish' => $faker->randomElement(['furnished', 'unfurnished', 'semifurnished']),
                    'condition' => $faker->randomElement(['second', 'new']),
                    'created_at' => $faker->dateTime(),
                    'category' => $faker->randomElement(['special']),
                    'description' => $faker->paragraph(80),
                    'developerid' => null,
                ]
            );
        });
    }
}