<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propietario_almacen', function (Blueprint $table) {
            $table->increments('propietario_almacen_id'); // PK autoincremental
            $table->string('propietario_almacen_nombre');
            $table->string('propietario_almacen_apellido');
            $table->string('propietario_almacen_direccion');
            $table->integer('propietario_almacen_estado')->default(1); // 1 activo, 0 inactivo
            $table->timestamps();
            $table->softDeletes(); // por si quieres borrado lógico
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propietario_almacen');
    }
};
