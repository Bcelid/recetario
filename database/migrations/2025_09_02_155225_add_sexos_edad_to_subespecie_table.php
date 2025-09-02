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
        Schema::table('subespecie', function (Blueprint $table) {
            $table->text('sexos')->after('subespecie_detalle');
            $table->integer('edad_min')->after('sexos');
            $table->integer('edad_max')->after('edad_min');
            $table->string('unidad_edad', 10)->after('edad_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subespecie', function (Blueprint $table) {
            $table->dropColumn(['sexos', 'edad_min', 'edad_max', 'unidad_edad']);
        });
    }
};
