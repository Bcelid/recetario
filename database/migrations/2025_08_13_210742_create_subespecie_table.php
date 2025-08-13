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
        Schema::create('subespecie', function (Blueprint $table) {
            $table->id('subespecie_id');
            $table->string('subespecie_nombre');
            $table->string('subespecie_cientifico');
            $table->unsignedBigInteger('especie_id');
            $table->integer('subespecie_estado');
            $table->text('subespecie_detalle')->nullable();
            $table->timestamps();

            // Clave foránea
            $table->foreign('especie_id')->references('especie_id')->on('especie')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subespecie');
    }
};
