<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTecnicosTable extends Migration
{
    public function up()
    {
        Schema::create('tecnico', function (Blueprint $table) {
            $table->increments('tecnico_id');
            $table->string('tecnido_cedula');
            $table->string('tecnico_nombre');
            $table->string('tecnico_apellido');
            $table->string('tecnico_email')->unique();
            $table->string('tecnico_telefono')->nullable();
            $table->unsignedInteger('categoria_id'); // también INT UNSIGNED

            $table->string('tecnico_senescyt')->nullable();
            $table->string('tecnico_estado')->default('1'); // o 'activo' o como manejes el estado
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('categoria_id')->references('tecnico_categoria_id')->on('tecnico_categoria');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tecnico');
    }
}
