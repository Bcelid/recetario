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
        Schema::create('unidad_medida_dosificacion', function (Blueprint $table) {
            $table->id('unidad_medida_dosificacion_id');
            $table->text('unidad_medida_dosificacion_representacion'); // Ej: L/ha
            $table->text('unidad_medida_dosificacion_detalle')->nullable(); // descripción
            $table->integer('unidad_medida_dosificacion_estado')->default(1); // 1 activo, 0 inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_medida_dosificacion');
    }
};
