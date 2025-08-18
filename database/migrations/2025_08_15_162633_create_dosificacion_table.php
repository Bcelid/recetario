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
        Schema::create('dosificacion', function (Blueprint $table) {
            $table->id('dosificacion_id');

            // Relaciones
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('cultivo_id')->nullable();
            $table->unsignedBigInteger('maleza_id')->nullable();
            $table->unsignedBigInteger('subespecie_id')->nullable();
            $table->unsignedBigInteger('unidad_medida_dosificacion_id')->nullable();

            // Otros campos
            $table->decimal('dosis', 8, 2)->nullable();
            $table->text('dosificacion_aplicacion')->nullable();

            $table->timestamps();

            // Claves foráneas
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
            $table->foreign('cultivo_id')->references('cultivo_id')->on('cultivo')->nullOnDelete();
            $table->foreign('maleza_id')->references('maleza_id')->on('maleza')->nullOnDelete();
            $table->foreign('subespecie_id')->references('subespecie_id')->on('subespecie')->nullOnDelete();
            $table->foreign('unidad_medida_dosificacion_id')->references('unidad_medida_dosificacion_id')->on('unidad_medida_dosificacion')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosificacion');
    }
};
