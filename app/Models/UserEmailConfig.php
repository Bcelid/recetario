<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UserEmailConfig extends Model
{
    protected $table = 'user_email_configs';

    protected $fillable = [
        'user_id',
        'smtp_provider',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_name',
        'smtp_from_address',
    ];

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mutador para cifrar la contraseña antes de guardar
    public function setSmtpPasswordAttribute($value)
    {
        $this->attributes['smtp_password'] = Crypt::encryptString($value);
    }

    // Accesor para descifrar la contraseña al acceder
    public function getSmtpPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null; // En caso de error de descifrado
        }
    }
}
