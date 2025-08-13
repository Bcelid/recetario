<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    protected $table = 'especie';
    protected $primaryKey = 'especie_id';
    public $timestamps = true;

    protected $fillable = [
        'especie_nombre',
        'especie_cientifico',
        'especie_estado',
        'especie_detalle',
    ];
    public function subespecies()
    {
        return $this->hasMany(Subespecie::class, 'especie_id', 'especie_id');
    }
}
