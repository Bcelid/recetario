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
        'almacen_logo',
    ];

    public function propietario()
    {
        return $this->belongsTo(PropietarioAlmacen::class, 'almacen_propietario_id', 'propietario_almacen_id');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'cliente_almacen_id', 'almacen_id');
    }

    public function recetaLotes()
    {
        return $this->hasMany(RecetaLote::class, 'almacen_id', 'almacen_id');
    }
}
