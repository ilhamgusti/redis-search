<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Redis\Client\ClientFacade;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';
    
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];

     /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Property $property) {

            $clientFacade = new ClientFacade();
            $client = $clientFacade->getClient(Redis::client());
            $index = new \MacFJA\RediSearch\Index('properties-idx', $client);

            $index->addDocumentFromArray($property->toArray(), 'properties:detail:'.$property->id);
        });
    }
}
