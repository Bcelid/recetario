<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredienteActivo extends Model
{
    use HasFactory;
    
    protected $table = 'ingredienteactivo';
    protected $primaryKey = 'ingrediente_activo_id';
    public $timestamps = true;

    protected $fillable = [
        'ingrediente_activo_nombre',
        'ingrediente_activo_detalle',
        'ingrediente_activo_estado',
    ];
}
