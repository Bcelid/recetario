<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cultivo extends Model
{
    protected $table = 'cultivo';
    protected $primaryKey = 'cultivo_id';
    public $timestamps = true;

    protected $fillable = [
        'cultivo_nombre',
        'cultivo_cientifico',
        'cultivo_estado',
        'cultivo_detalle',
    ];

    public function dosificaciones()
    {
        return $this->hasMany(Dosificacion::class, 'cultivo_id', 'cultivo_id');
    }
}
