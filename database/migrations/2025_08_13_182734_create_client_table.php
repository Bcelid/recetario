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
        Schema::create('cliente', function (Blueprint $table) {
            $table->id('cliente_id');
            $table->string('cliente_cedula');
            $table->string('cliente_nombre');
            $table->string('cliente_apellido');
            $table->integer('cliente_estado')->default(1);
            $table->unsignedBigInteger('cliente_almacen_id');
            $table->string('cliente_direccion')->nullable();
            $table->timestamps();
            
            // Agregar la restricción única sobre la combinación de cliente_cedula y cliente_almacen_id
            $table->unique(['cliente_cedula', 'cliente_almacen_id']); 

            $table->foreign('cliente_almacen_id')
                  ->references('almacen_id')->on('almacen')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente');
    }
};
