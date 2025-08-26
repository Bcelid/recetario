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
        Schema::table('almacen', function (Blueprint $table) {
            $table->string('almacen_logo')->nullable()->after('almacen_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('almacen', function (Blueprint $table) {
            $table->dropColumn('almacen_logo');
        });
    }
};
