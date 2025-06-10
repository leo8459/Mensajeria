<?php

// app/Models/Eventos.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'codigo',
        'telefono',
        'user_id',
        'nombre',
    ];

    /** Relación con usuarios (opcional para mostrar el nombre) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
