<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lower_thirds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('template')->default('slide_left');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lower_thirds');
    }
};
