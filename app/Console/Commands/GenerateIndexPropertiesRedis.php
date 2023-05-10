<?php

namespace App\Console\Commands;

use App\Library\PropertiesIndex;
use Illuminate\Console\Command;

class GenerateIndexPropertiesRedis extends Command
{
    protected PropertiesIndex $properties;

    public function __construct(PropertiesIndex $properties) {
        parent::__construct();
        $this->properties = $properties;
    }
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:properties-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate index properties redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generate index properties...');
        $buildindex = $this->properties->buildIndex();
        $this->info("Generate index properties $buildindex");
    }
}
