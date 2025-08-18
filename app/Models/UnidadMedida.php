<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidad_medida';
    protected $primaryKey = 'unidad_medida_id';
    public $timestamps = true;

    protected $fillable = [
        'unidad_medida_detalle',
        'unidad_medida_estado',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id', 'unidad_medida_id');
    }

    public function productosIngredientes()
    {
        return $this->hasMany(ProductoIngrediente::class, 'unidad_medida_id', 'unidad_medida_id');
    }
}
