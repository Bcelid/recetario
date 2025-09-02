<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('receta_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receta_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('dosificacion_id');
            $table->float('producto_cantidad');
            $table->timestamps();

            // Foreign keys
            $table->foreign('receta_id')->references('receta_id')->on('receta')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('restrict');
            $table->foreign('dosificacion_id')->references('dosificacion_id')->on('dosificacion')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receta_producto');
    }
};
