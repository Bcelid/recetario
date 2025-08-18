<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoIngrediente extends Model
{
    use HasFactory;

    protected $table = 'producto_ingrediente';
    protected $primaryKey = 'producto_ingrediente_id';
    public $timestamps = true;

    protected $fillable = [
        'producto_id',
        'ingrediente_activo_id',
        'cantidad',
        'unidad_medida_id',
    ];

    // Relaciones

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    public function ingredienteActivo()
    {
        return $this->belongsTo(IngredienteActivo::class, 'ingrediente_activo_id', 'ingrediente_activo_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id', 'unidad_medida_id');
    }
}
