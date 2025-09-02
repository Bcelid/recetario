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
        Schema::create('receta_lote_envios', function (Blueprint $table) {
            $table->id();

            // Relación con usuario que envía
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Relación con receta_lote
            $table->unsignedBigInteger('receta_lote_id');
            $table->foreign('receta_lote_id')->references('receta_lote_id')->on('receta_lote')->onDelete('cascade');

            // Relación con almacen (para trazabilidad)
            $table->unsignedBigInteger('almacen_id');
            $table->foreign('almacen_id')->references('almacen_id')->on('almacen')->onDelete('cascade');

            // Correo al que se envió (se guarda como snapshot)
            $table->string('correo');

            // URL del documento adjunto
            $table->string('url_documento');

            // Texto del mensaje enviado (body del correo, opcional)
            $table->text('mensaje')->nullable();

            // Número de envío (1 = primer envío, 2 = reenvío, etc.)
            $table->unsignedInteger('numero_envio')->default(1);

            // Fecha y hora exacta del envío
            $table->timestamp('fecha_envio')->useCurrent();

            // Estado del envío (opcional: enviado, fallido, pendiente)
            $table->string('estado')->default('enviado');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receta_lote_envios');
    }
};
