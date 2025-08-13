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
        Schema::create('cultivo', function (Blueprint $table) {
            $table->id('cultivo_id'); // PK
            $table->string('cultivo_nombre');
            $table->string('cultivo_cientifico');
            $table->integer('cultivo_estado');
            $table->text('cultivo_detalle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultivo');
    }
};
