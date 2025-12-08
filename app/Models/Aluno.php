<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    use HasFactory;

    protected $table = 'alunos';
    protected $primaryKey = 'id_perfil';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_perfil',      // ou 'id_usuario'
        'id_instituicao',
        'rm',
        'data_nascimento',
        'cpf'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_perfil', 'id');
    }
}