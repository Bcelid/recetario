<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id('producto_id');

            $table->string('producto_nombre');
            $table->string('producto_concentracion');
            $table->decimal('producto_presentacion', 10, 2);

            $table->unsignedBigInteger('unidad_medida_id');
            $table->unsignedBigInteger('formulacion_id');

            $table->text('producto_diagnostico')->nullable();
            $table->integer('producto_unidad_en_envase')->nullable(); // campo opcional
            $table->integer('producto_tipo'); // 0: agrícola, 1: veterinario
            $table->integer('producto_estado')->default(1); // activo por defecto

            $table->timestamps();

            // Foreign keys
            $table->foreign('unidad_medida_id')->references('unidad_medida_id')->on('unidad_medida');
            $table->foreign('formulacion_id')->references('formulacion_id')->on('formulacion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto');
    }
};
