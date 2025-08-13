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
        Schema::create('especie', function (Blueprint $table) {
            $table->id('especie_id');
            $table->string('especie_nombre');
            $table->string('especie_cientifico');
            $table->integer('especie_estado');
            $table->text('especie_detalle')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('especie');
    }
};
