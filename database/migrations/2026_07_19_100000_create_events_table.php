<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('tagline_ar')->nullable();
            $table->string('tagline_en')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('venue_name_ar')->nullable();
            $table->string('venue_name_en')->nullable();
            $table->string('venue_address_ar')->nullable();
            $table->string('venue_address_en')->nullable();
            $table->string('map_embed_url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
