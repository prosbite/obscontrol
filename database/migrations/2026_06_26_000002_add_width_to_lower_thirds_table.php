<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lower_thirds', function (Blueprint $table) {
            $table->string('width')->nullable()->default('10vw')->after('template');
        });
    }

    public function down(): void
    {
        Schema::table('lower_thirds', function (Blueprint $table) {
            $table->dropColumn('width');
        });
    }
};
