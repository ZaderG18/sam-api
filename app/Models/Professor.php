<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant; // Se você estiver usando o Trait de multi-tenancy

class Professor extends Model
{
    use HasFactory;
    // use BelongsToTenant; // Descomente se já criou o Trait

    // Define o nome da tabela (o Laravel tentaria 'professors', mas criamos 'professores')
    protected $table = 'professores';

    // A chave primária no nosso banco é 'id_perfil', não 'id'
    protected $primaryKey = 'id_perfil';
    public $incrementing = false; // Como é UUID/FK, não é auto-incremento
    protected $keyType = 'string';

    // Campos que podem ser preenchidos via create()
    protected $fillable = [
        'id_perfil',        // ou 'id_usuario', verifique como ficou na sua migration
        'id_instituicao',
        'registro_funcional',
        'formacao',
        'departamento'
    ];

    // Relacionamento com o Usuário/Perfil pai
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_perfil', 'id');
    }
}