<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('field_key');
            $table->text('value_ar')->nullable();
            $table->text('value_en')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'section', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_content');
    }
};
