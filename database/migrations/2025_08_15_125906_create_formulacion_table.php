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
        Schema::create('formulacion', function (Blueprint $table) {
            $table->id('formulacion_id');
            $table->string('formulacion_nombre', 255);
            $table->string('formulacion_abreviatura', 100)->nullable();
            $table->boolean('formulacion_estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulacion');
    }
};
