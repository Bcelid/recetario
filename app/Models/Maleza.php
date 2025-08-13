<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maleza extends Model
{
    protected $table = 'maleza'; // Nombre de la tabla

    protected $primaryKey = 'maleza_id'; // Clave primaria

    protected $fillable = [
        'maleza_nombre',
        'maleza_cientifico',
        'maleza_estado',
        'maleza_detalle',
    ];

}
