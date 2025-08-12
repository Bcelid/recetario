<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tecnico_firma', function (Blueprint $table) {
            $table->id('tecnico_firma_id');
            $table->string('tecnico_firma_nombre');
            $table->string('tecnico_firma_ruta')->nullable();
            $table->string('tecnico_firma_clave')->nullable();
            $table->boolean('tecnico_firma_estado')->default(1);
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_expiracion')->nullable();

            // FK a tecnico.tecnico_id
            $table->unsignedInteger('tecnico_id');

            $table->foreign('tecnico_id')
                  ->references('tecnico_id')
                  ->on('tecnico')
                  ->onDelete('cascade');

            $table->timestamps(); // si quieres manejar created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecnico_firma');
    }
};
