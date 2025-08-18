<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosificacion extends Model
{
    use HasFactory;

    protected $table = 'dosificacion';
    protected $primaryKey = 'dosificacion_id';
    public $timestamps = true;

    protected $fillable = [
        'producto_id',
        'cultivo_id',
        'maleza_id',
        'subespecie_id',
        'dosis',
        'unidad_medida_dosificacion_id',
        'dosificacion_aplicacion',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id', 'cultivo_id');
    }

    public function maleza()
    {
        return $this->belongsTo(Maleza::class, 'maleza_id', 'maleza_id');
    }

    public function subespecie()
    {
        return $this->belongsTo(Subespecie::class, 'subespecie_id', 'subespecie_id');
    }

    public function unidadMedidaDosificacion()
    {
        return $this->belongsTo(UnidadMedidaDosificacion::class, 'unidad_medida_dosificacion_id', 'unidad_medida_dosificacion_id');
    }
}
