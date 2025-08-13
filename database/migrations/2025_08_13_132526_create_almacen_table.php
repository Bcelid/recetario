<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacen', function (Blueprint $table) {
            $table->id('almacen_id');
            $table->unsignedInteger('almacen_propietario_id');

            $table->string('almacen_direccion')->nullable();
            $table->string('almacen_telefono', 20)->nullable();
            $table->string('almacen_correo')->nullable();
            $table->tinyInteger('almacen_estado')->default(1);
            $table->string('almacen_nombre');

            $table->timestamps();

            // Relación con propietario
            $table->foreign('almacen_propietario_id')
                  ->references('propietario_almacen_id')
                  ->on('propietario_almacen')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacen');
    }
};
