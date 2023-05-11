<?php

use App\Library\PropertiesIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Index;


return new class extends Migration
{

    protected $properties;

    public function __construct() {
        $this->properties = new PropertiesIndex();
    }
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->properties->buildIndex();
    }

    
};
