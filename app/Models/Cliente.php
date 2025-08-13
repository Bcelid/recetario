<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente';
    protected $primaryKey = 'cliente_id';

    protected $fillable = [
        'cliente_cedula',
        'cliente_nombre',
        'cliente_apellido',
        'cliente_estado',
        'cliente_almacen_id',
        'cliente_direccion',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'cliente_almacen_id', 'almacen_id');
    }
}
