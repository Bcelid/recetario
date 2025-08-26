<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'producto';
    protected $primaryKey = 'producto_id';
    public $timestamps = true;

    protected $fillable = [
        'producto_nombre',
        'producto_concentracion',
        'producto_presentacion',
        'unidad_medida_id',
        'formulacion_id',
        'producto_estado',
        'producto_diagnostico',
        'producto_unidad_en_envase',
        'producto_tipo',
    ];

    // Relaciones
    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id', 'unidad_medida_id');
    }

    public function formulacion()
    {
        return $this->belongsTo(Formulacion::class, 'formulacion_id', 'formulacion_id');
    }

    public function ingredientes()
    {
        return $this->hasMany(ProductoIngrediente::class, 'producto_id', 'producto_id');
    }

    public function dosificaciones()
    {
        return $this->hasMany(Dosificacion::class, 'producto_id', 'producto_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'producto_id', 'producto_id');
    }
}
