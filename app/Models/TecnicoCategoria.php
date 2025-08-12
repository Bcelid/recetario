<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TecnicoCategoria extends Model
{
    protected $table = 'tecnico_categoria';

    protected $primaryKey = 'tecnico_categoria_id';

    public $timestamps = true;

    // Para soft delete lógico usando campo booleano
    // No uses SoftDeletes nativo de Laravel aquí porque usas campo booleano

    protected $fillable = [
        'tecnico_categoria_nombre',
        'tecnico_categoria_estado',
    ];

    // Scope para filtrar solo activos
    public function scopeActivo($query)
    {
        return $query->where('tecnico_categoria_estado', 1);
    }
     public function tecnicos()
    {
        return $this->hasMany(Tecnico::class, 'categoria_id', 'tecnico_categoria_id');
    }
}
