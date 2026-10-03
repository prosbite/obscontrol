<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bible_passages', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('translation');
            $table->string('reference_key', 255)->unique();
            $table->string('label');
            $table->longText('text');
            $table->string('translation_abbr')->nullable();
            $table->text('copyright')->nullable();
            $table->json('verses')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'translation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bible_passages');
    }
};
