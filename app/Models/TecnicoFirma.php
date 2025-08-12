<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TecnicoFirma extends Model
{
    protected $table = 'tecnico_firma';

    protected $primaryKey = 'tecnico_firma_id';

    public $timestamps = false; // no veo created_at ni updated_at

    protected $fillable = [
        'tecnico_firma_nombre',
        'tecnico_firma_ruta',
        'tecnico_firma_clave',
        'tecnico_firma_estado',
        'fecha_emision',
        'fecha_expiracion',
        'tecnico_id',
    ];

    // Relación con técnico
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id', 'tecnico_id');
    }

    // Scope para activos
    public function scopeActivas($query)
    {
        return $query->where('tecnico_firma_estado', 1);
    }
}
