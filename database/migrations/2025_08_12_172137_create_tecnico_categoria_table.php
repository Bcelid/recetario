<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTecnicoCategoriaTable extends Migration
{
    public function up()
    {
        Schema::create('tecnico_categoria', function (Blueprint $table) {
            $table->id('tecnico_categoria_id');
            $table->string('tecnico_categoria_nombre');
            $table->boolean('tecnico_categoria_estado')->default(1); // Para softdelete lógico
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tecnico_categoria');
    }
}
