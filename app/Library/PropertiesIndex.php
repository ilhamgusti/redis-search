<?php 

namespace App\Library;

use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Index;


class PropertiesIndex {

	private $client;
    private $indexName = 'properties-idx';
	private $prefixes;
    private $indexBuilder;

	public function __construct() {
		$this->client = (new ClientFacade())->getClient(Redis::client());
        $this->prefixes = [config('database.redis.options.prefix').config('app.properties')];
        $this->indexBuilder = new IndexBuilder();
	}

	public function buildIndex()
	{
		try {
			$this->down();
			$this->client = (new ClientFacade())->getClient(Redis::client());
			return $this->createindex();
		} catch (\Throwable $th) {
			$this->client = (new ClientFacade())->getClient(Redis::client());
			return $this->createindex();
		}
		
	}


    private function down(): void
    {
        $index = new Index($this->indexName,$this->client);
        $index->delete();
    }

	private function createindex() {
			$this->client = (new ClientFacade())->getClient(Redis::client());
			return $this->indexBuilder
					->setPrefixes($this->prefixes)
					->setIndex($this->indexName)
					->addTagField('id', sortable: true)
					->addTextField('title')
					->addTextField('address')
					->addTagField('location', separator: ',')
					->addNumericField('price', sortable: true)
					->addNumericField('landArea')
					->addNumericField('buildingSize')
					->addNumericField('bedroom')
					->addNumericField('bathroom')
					->addTagField('certificate', separator: ',')
					->addTagField('type', separator: ',')
					->addTagField('furnish', separator: ',')
					->addTagField('condition', separator: ',')
					->addTagField('category', separator: ',')
					->addNumericField('created_at', sortable: true)
					->addTextField('description')
					->create($this->client);
	}
	
}