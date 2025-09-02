<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'receta';
    protected $primaryKey = 'receta_id';

    protected $fillable = [
        'receta_lote_id',
        'cliente_id',
        'fecha_emision',
        'receta_numero',
    ];

    // Relaciones
    public function recetaLote()
    {
        return $this->belongsTo(RecetaLote::class, 'receta_lote_id', 'receta_lote_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    public function productos()
    {
        return $this->hasMany(RecetaProducto::class, 'receta_id', 'receta_id');
    }
}
