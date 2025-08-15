<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulacion extends Model
{
    use HasFactory;

    protected $table = 'formulacion';
    protected $primaryKey = 'formulacion_id';
    public $timestamps = true;

    protected $fillable = [
        'formulacion_nombre',
        'formulacion_abreviatura',
        'formulacion_estado',
    ];
}
