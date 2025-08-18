<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->id('producto_ingrediente_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('ingrediente_activo_id');
            $table->decimal('cantidad', 8, 2);
            $table->unsignedBigInteger('unidad_medida_id');
            $table->timestamps();

            // Relaciones
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
            $table->foreign('ingrediente_activo_id')->references('ingrediente_activo_id')->on('ingredienteactivo')->onDelete('restrict');
            $table->foreign('unidad_medida_id')->references('unidad_medida_id')->on('unidad_medida')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_ingrediente');
    }
};
