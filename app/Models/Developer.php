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
        static::created(function (Developer $developer) {
            Redis::hMSet('developer:detail:'.$developer->id, $developer->toArray());
        });
    }
}
