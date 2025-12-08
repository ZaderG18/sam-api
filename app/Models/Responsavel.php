<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model
{
    use HasFactory;
    protected $table = 'responsaveis'; // Nome da tabela no banco
    protected $primaryKey = 'id_perfil';
    public $incrementing = false;
    protected $fillable = ['id_perfil', 'id_instituicao', 'cpf', 'endereco'];
}