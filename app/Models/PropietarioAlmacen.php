<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropietarioAlmacen extends Model
{
    use SoftDeletes;

    protected $table = 'propietario_almacen';
    protected $primaryKey = 'propietario_almacen_id';
    public $timestamps = true;

    protected $fillable = [
        'propietario_almacen_nombre',
        'propietario_almacen_apellido',
        'propietario_almacen_direccion',
        'propietario_almacen_estado',
    ];
}
