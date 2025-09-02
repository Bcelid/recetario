<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaProducto extends Model
{
    use HasFactory;

    protected $table = 'receta_producto';
    protected $primaryKey = 'id';

    protected $fillable = [
        'receta_id',
        'producto_id',
        'dosificacion_id',
        'producto_cantidad',
    ];

    // Relaciones
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id', 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    public function dosificacion()
    {
        return $this->belongsTo(Dosificacion::class, 'dosificacion_id', 'dosificacion_id');
    }
}