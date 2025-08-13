<?php

// app/Models/Almacen.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacen';
    protected $primaryKey = 'almacen_id';

    protected $fillable = [
        'almacen_propietario_id',
        'almacen_direccion',
        'almacen_telefono',
        'almacen_correo',
        'almacen_estado',
        'almacen_nombre',
    ];

    public function propietario()
    {
        return $this->belongsTo(PropietarioAlmacen::class, 'almacen_propietario_id', 'propietario_almacen_id');
    }
}
