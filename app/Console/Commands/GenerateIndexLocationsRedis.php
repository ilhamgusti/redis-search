<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Index;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use DB;
use Str;

class GenerateIndexLocationsRedis extends Command
{
    private $client;
    private $indexName = 'location-idx';

    private $prefix = "location:detail:";
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
    protected $signature = 'app:locations-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate index locations redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generate index locations...');
        $buildindex = $this->buildIndex();
        $this->info("index locations $buildindex generated!");
    }

    public function buildIndex()
    {
        $this->preSync();
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
            ->addTagField('id', sortable: true)
            ->addTextField('name')
            ->addTagField('type', separator: ',')
            ->addTagField('refName', separator: ',')
            ->create($this->client);
    }

    public function preSync()
    {
        $kecamatan = DB::table('reg_provinces')
            ->selectRaw("reg_provinces.name AS provinsi, reg_regencies.name AS kabupaten_kota, reg_districts.name AS kecamatan, reg_districts.id AS kecid")
            ->leftJoin('reg_regencies', 'reg_regencies.province_id', '=', 'reg_provinces.id')
            ->leftJoin('reg_districts', 'reg_districts.regency_id', '=', 'reg_regencies.id')
            ->get()->map(function ($data) {
                return [
                    'id' => "kec-{$data->kecid}",
                    'name' => $data->kecamatan,
                    'type' => 'Kecamatan',
                    'refName' => $data->kabupaten_kota . "," . $data->provinsi
                ];
            })->each(function ($data) {
                Redis::hMSet($this->prefix . $data['id'], $data);
            });


        $kabupaten = DB::table('reg_regencies')
            ->selectRaw("reg_provinces.name AS provinsi, reg_regencies.name AS kabupaten_kota, reg_regencies.id AS kabid")
            ->leftJoin('reg_provinces', 'reg_provinces.id', '=', 'reg_regencies.province_id')
            ->get()->map(function ($data) {
                return [
                    'id' => "kab-{$data->kabid}",
                    'name' => $data->kabupaten_kota,
                    'type' => 'Kabupaten / Kota',
                    'refName' => $data->provinsi
                ];
            })->each(function ($data) {
            Redis::hMSet($this->prefix . $data['id'], $data);
        });

        $provinsi = DB::table('reg_provinces')->get()->map(function ($data) {
            return [
                'id' => 'prov-' . $data->id,
                'name' => $data->name,
                'type' => 'Provinsi',
                'refName' => 'Indonesia'
            ];
        })->each(function ($data) {
            Redis::hMSet($this->prefix . $data['id'], $data);
        });
    }
}