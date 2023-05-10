<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Redis\Client\ClientFacade;

return new class extends Migration {

    private $client;
    private $indexName = 'properties-idx';
    private $prefixes;

    public function __construct()
    {
        $this->client = (new ClientFacade())->getClient(Redis::client());
        $this->prefixes = [config('database.redis.options.prefix').'properties:detail:'];
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $builder = new \MacFJA\RediSearch\IndexBuilder();
        $builder
            ->setPrefixes($this->prefixes)
            ->setIndex('properties-idx')
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
            ->create($this->client);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $index = new \MacFJA\RediSearch\Index($this->indexName, $this->client);
        $index->delete();
    }
};