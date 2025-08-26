<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tecnico extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'tecnico_id';
    protected $table = 'tecnico';


    protected $fillable = [
        'tecnido_cedula',
        'tecnico_nombre',
        'tecnico_apellido',
        'tecnico_email',
        'tecnico_telefono',
        'categoria_id',
        'tecnico_senescyt',
        'tecnico_estado',
    ];

    public function categoria()
    {
        return $this->belongsTo(TecnicoCategoria::class, 'categoria_id', 'tecnico_categoria_id');
    }

    public function firmas()
    {
        return $this->hasMany(TecnicoFirma::class, 'tecnico_id', 'tecnico_id');
    }

    public function recetaLotes()
    {
        return $this->hasMany(RecetaLote::class, 'tecnico_id', 'tecnico_id');
    }
}
