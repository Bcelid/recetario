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
        Schema::create('receta_lote', function (Blueprint $table) {
            $table->id('receta_lote_id');

            $table->unsignedInteger('tecnico_id');
            $table->unsignedBigInteger('almacen_id');


            $table->unsignedBigInteger('receta_tipo'); // 0: agrícola, 1: veterinaria

            $table->date('fecha_creacion');

            $table->unsignedTinyInteger('receta_lote_estado');

            $table->string('receta_lote_path')->nullable(); // PDF generado (opcional)
            $table->boolean('receta_lote_firmado')->default(false);
            $table->boolean('receta_lote_enviado')->default(false);

            $table->timestamp('receta_lote_fecha_envio')->nullable();
            $table->timestamp('receta_lote_ultimo_envio')->nullable();

            $table->timestamps();

            // Relaciones
            $table->foreign('tecnico_id')->references('tecnico_id')->on('tecnico')->onDelete('cascade');
            $table->foreign('almacen_id')->references('almacen_id')->on('almacen')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_lote');
    }
};
