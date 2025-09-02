<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receta', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['dosificacion_id']);

            // Luego elimina las columnas
            $table->dropColumn(['producto_id', 'dosificacion_id', 'producto_cantidad']);
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receta', function (Blueprint $table) {
            //
        });
    }
};
