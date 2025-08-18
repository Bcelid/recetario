<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedidaDosificacion extends Model
{
    use HasFactory;

    protected $table = 'unidad_medida_dosificacion';
    protected $primaryKey = 'unidad_medida_dosificacion_id';
    public $timestamps = true;

    protected $fillable = [
        'unidad_medida_dosificacion_representacion',
        'unidad_medida_dosificacion_detalle',
        'unidad_medida_dosificacion_estado',
    ];

    public function dosificaciones()
    {
        return $this->hasMany(Dosificacion::class, 'unidad_medida_dosificacion_id', 'unidad_medida_dosificacion_id');
    }
}
