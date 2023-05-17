<?php

namespace App\Console\Commands;

use App\Library\DeveloperIndex;
use Illuminate\Console\Command;

class GenerateIndexDeveloperRedis extends Command
{

    protected DeveloperIndex $developerindex;


    public function __construct(DeveloperIndex $developerIndex) {
         parent::__construct();
         $this->developerindex = $developerIndex;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:developer-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate developer index redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('generate index developer');
        $buildindex=$this->developerindex->buildIndex();
        $this->info("Generate index properties $buildindex");
    }
}
