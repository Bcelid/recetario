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
         Schema::create('tecnico_firma', function (Blueprint $table) {
            $table->string('tecnico_firma_key')->nullable();
            $table->string('tecnico_firma_pub')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::create('tecnico_firma', function (Blueprint $table) {
            $table->dropColumn(['tecnico_firma_key', 'tecnico_firma_pub']);
        });
    }
};
