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
        Schema::create('receta', function (Blueprint $table) {
            $table->id('receta_id');

            $table->unsignedBigInteger('receta_lote_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('dosificacion_id'); // << nuevo campo clave foránea

            $table->unsignedInteger('producto_cantidad');

            $table->date('fecha_emision');
            $table->unsignedInteger('receta_numero')->nullable();

            $table->timestamps();

            // Relaciones
            $table->foreign('receta_lote_id')->references('receta_lote_id')->on('receta_lote')->onDelete('cascade');
            $table->foreign('cliente_id')->references('cliente_id')->on('cliente')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
            $table->foreign('dosificacion_id')->references('dosificacion_id')->on('dosificacion')->onDelete('cascade'); // relación directa a la dosificación
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta');
    }
};
