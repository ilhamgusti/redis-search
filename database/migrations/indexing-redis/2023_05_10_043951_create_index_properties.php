<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Index;


return new class extends Migration
{

    private $client;
    private $indexName = 'properties-idx';
    private $prefixes;
    private $indexBuilder;
   
    public function __construct()
    {
        $this->client = (new ClientFacade())->getClient(Redis::client());
        $this->prefixes = [config('database.redis.options.prefix').'property:'];
        $this->indexBuilder = new IndexBuilder();
    }
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->client = (new ClientFacade())->getClient(Redis::client());
       
        $this->indexBuilder
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
            ->addTextField('certificate')
            ->addTextField('type')
            ->addTextField('furnish')
            ->addTextField('condition')
            ->addTextField('category')
            ->addNumericField('created_at', sortable: true)
            ->addTextField('description')
            ->create($this->client);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $index = new Index($this->indexName,$this->client);
        $index->delete();
    }
};
