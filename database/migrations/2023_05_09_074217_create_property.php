<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('address');
            $table->string('location');
            $table->bigInteger('price');
            $table->integer('landArea');
            $table->integer('buildingSize');
            $table->integer('bedroom');
            $table->integer('bathroom');
            $table->string('certificate');
            $table->string('type');
            $table->string('furnish');
            $table->string('condition');
            $table->string('category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
