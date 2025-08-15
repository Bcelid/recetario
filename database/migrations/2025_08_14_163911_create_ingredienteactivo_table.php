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
        Schema::create('ingredienteactivo', function (Blueprint $table) {
            $table->id('ingrediente_activo_id');
            $table->string('ingrediente_activo_nombre');
            $table->text('ingrediente_activo_detalle')->nullable();
            $table->integer('ingrediente_activo_estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredienteactivo');
    }
};
