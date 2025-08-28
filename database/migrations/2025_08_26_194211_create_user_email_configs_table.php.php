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
        Schema::create('user_email_configs', function (Blueprint $table) {
            $table->id();

            // Relación con tabla users
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Nombre del proveedor (solo para referencia, no se usa en la conexión)
            $table->string('smtp_provider')->nullable()->comment('Proveedor del servicio, ej: gmail, outlook');

            // Datos de conexión SMTP
            $table->string('smtp_host')->nullable()->comment('Host SMTP, ej: smtp.gmail.com');
            $table->integer('smtp_port')->nullable()->comment('Puerto SMTP, ej: 587 o 465');
            $table->string('smtp_encryption')->nullable()->comment('Tipo de encriptación: ssl, tls o null');

            // Credenciales de autenticación
            $table->string('smtp_username')->nullable()->comment('Correo del remitente');
            $table->string('smtp_password')->nullable()->comment('Clave de aplicación o contraseña SMTP');

            // Información de remitente
            $table->string('smtp_from_name')->nullable()->comment('Nombre que aparece como remitente');
            $table->string('smtp_from_address')->nullable()->comment('Correo que aparece como remitente');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_email_configs');
    }
};
