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
        Schema::create('maleza', function (Blueprint $table) {
            $table->id('maleza_id'); // Clave primaria
            $table->string('maleza_nombre'); // Nombre común
            $table->string('maleza_cientifico'); // Nombre científico
            $table->integer('maleza_estado'); // Estado de registro (activo/inactivo u otro)
            $table->text('maleza_detalle')->nullable(); // Detalles adicionales
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('maleza');
    }
};
