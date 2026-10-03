<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scriptures', function (Blueprint $table) {
            $table->string('provider')->nullable();
            $table->string('translation_abbr')->nullable();
            $table->string('canonical_reference')->nullable();
            $table->timestamp('fetched_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('scriptures', function (Blueprint $table) {
            $table->dropColumn(['provider', 'translation_abbr', 'canonical_reference', 'fetched_at']);
        });
    }
};
