<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaLote extends Model
{
    use HasFactory;

    protected $table = 'receta_lote';
    protected $primaryKey = 'receta_lote_id';

    protected $fillable = [
        'tecnico_id',
        'almacen_id',
        'receta_tipo',
        'fecha_creacion',
        'receta_lote_estado',
        'receta_lote_path',
        'receta_lote_firmado',
        'receta_lote_enviado',
        'receta_lote_fecha_envio',
        'receta_lote_ultimo_envio',
    ];

    // Relaciones

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id', 'tecnico_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'almacen_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'receta_lote_id', 'receta_lote_id');
    }
}
