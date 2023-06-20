<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Index;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use DB;
use Str;

class GenerateIndexProductRedis extends Command
{
    private $client;
    private $indexName = 'product-idx';

    private $prefix = "product:";
    private $prefixes;
    private $indexBuilder;

    public function __construct()
    {
        parent::__construct();
        $this->client = (new ClientFacade())->getClient(Redis::client());
        $this->prefixes = [config('database.redis.options.prefix') . $this->prefix];
        $this->indexBuilder = new IndexBuilder();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate index product redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generate index product...');
        $buildindex = $this->buildIndex();
        $this->info("index product $buildindex generated!");
    }

    public function buildIndex()
    {
        try {
            $this->down();
            $this->client = (new ClientFacade())->getClient(Redis::client());
            $this->createindex();
            return 'OK';
        } catch (\Throwable $th) {
            $this->client = (new ClientFacade())->getClient(Redis::client());
            return $this->createindex();
        }

    }


    private function down(): void
    {
        $index = new Index($this->indexName, $this->client);
        $index->delete();
    }

    private function createindex()
    {
        $this->client = (new ClientFacade())->getClient(Redis::client());
        return $this->indexBuilder
            ->setPrefixes($this->prefixes)
            ->setIndex($this->indexName)
            ->addTextField('nama')
            ->addNumericField('harga',sortable: true)
            ->addTagField('idkategori', separator: ',',sortable: true)
			->create($this->client);
    }

    
}