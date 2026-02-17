<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'username',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'nro_documento',
        'tipo_documento',
        'telefono',
        'password'
    ];

    /**
     * Los atributos que deben estar ocultos
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Relación con cliente
    public function client()
    {
        return $this->hasOne(Client::class);
    }
    // Relacion con Admin
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function isAdmin()
    {
        return $this->admin !== null;
    }

    public function isSuperAdmin()
    {
        return $this->admin && $this->admin->isSuperAdmin();
    }
    // Relacion con Staff
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }
}
