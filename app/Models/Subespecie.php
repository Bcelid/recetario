<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subespecie extends Model
{
    protected $table = 'subespecie';
    protected $primaryKey = 'subespecie_id';
    public $timestamps = true;

    protected $casts = [
        'sexos' => 'array',
    ];

    protected $fillable = [
        'subespecie_nombre',
        'subespecie_cientifico',
        'subespecie_estado',
        'subespecie_detalle',
        'especie_id',
        'sexos',
        'edad_min',
        'edad_max',
        'unidad_edad',
    ];

    /**
     * Relación: Subespecie pertenece a una Especie
     */
    public function especie()
    {
        return $this->belongsTo(Especie::class, 'especie_id', 'especie_id');
    }

    public function dosificaciones()
    {
        return $this->hasMany(Dosificacion::class, 'subespecie_id', 'subespecie_id');
    }
}
