<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'estado',
    ];

    public $timestamps = true;

    // Relación con usuarios (si hay)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
