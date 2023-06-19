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
        /**
         * Handle the Properties "created" event.
         */
        static::created(function (Property $property) {

            Redis::hMSet('properties:detail:'.$property->id, $property->toArray());
        });

        /**
         * Handle the Properties "updated" event.
         */
        static::updated(function (Property $property) {

            Redis::hMSet('properties:detail:'.$property->id, $property->toArray());
        });

        /**
         * Handle the Properties "deleted" event.
         */
        static::deleted(function (Property $property) {
            Redis::delete('properties:detail:'.$property->id);
        });
    }
}
