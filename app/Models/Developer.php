<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Developer extends Model
{
    use HasFactory;


    protected $table = 'developer';
    
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
        static::created(function (Developer $developer) {
            Redis::hMSet('developer:detail:'.$developer->id, $developer->toArray());
        });

        /**
         * Handle the Developer "updated" event.
         */
        static::updated(function (Developer $data) {

            Redis::hMSet('developer:detail:'.$data->id, $data->toArray());
        });

        /**
         * Handle the Developer "deleted" event.
         */
        static::deleted(function (Developer $data) {
            Redis::delete('developer:detail:'.$data->id);
        });
    }
}
