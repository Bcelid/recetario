<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaLoteEnvio extends Model
{
    protected $table = 'receta_lote_envios';

    protected $fillable = [
        'user_id',
        'receta_lote_id',
        'almacen_id',
        'correo',
        'url_documento',
        'mensaje',
        'numero_envio',
        'fecha_envio',
        'estado'
    ];

    public function recetaLote()
    {
        return $this->belongsTo(RecetaLote::class, 'receta_lote_id', 'receta_lote_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'almacen_id');
    }
}
