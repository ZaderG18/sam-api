<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Responsavel; // <--- Importante: Crie este Model se não existir
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação de Segurança
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // ADICIONADO: 'responsavel' na lista permitida
            'tipo_acesso' => 'required|in:aluno,professor,coordenador,diretor,responsavel',
            'matricula' => 'required_if:tipo_acesso,aluno',
            'departamento' => 'required_if:tipo_acesso,professor',
            'cpf' => 'nullable|string',      // O front envia
            'telefone' => 'nullable|string', // O front envia
        ]);

        // 2. Início da Transação
        return DB::transaction(function () use ($validated, $request) {
            
            // Lógica de Fallback para testes (se não estiver logado, usa ID 1)
            $userLogado = $request->user();
            $instituicaoId = $userLogado ? $userLogado->id_instituicao : 1; 

            // A. Cria o Usuário de Acesso (Login)
            $user = User::create([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'id_instituicao' => $instituicaoId,
                'tipo_usuario' => $validated['tipo_acesso'],
                'password' => Hash::make(Str::random(12)),
                'telefone' => $validated['telefone'] ?? null, // Salvando telefone
            ]);

            // B. Cria os Dados Específicos (Tabelas Satélites)
            
            // --- ALUNO ---
            if ($validated['tipo_acesso'] === 'aluno') {
                Aluno::create([
                    'id_perfil' => $user->id,
                    'id_instituicao' => $instituicaoId,
                    'rm' => $validated['matricula'],
                    'cpf' => $validated['cpf'] ?? null, // Salvando CPF do aluno
                ]);
            } 
            // --- PROFESSOR ---
            elseif ($validated['tipo_acesso'] === 'professor') {
                Professor::create([
                    'id_perfil' => $user->id,
                    'id_instituicao' => $instituicaoId,
                    'departamento' => $validated['departamento'] ?? null,
                ]);
            }
            // --- RESPONSÁVEL (Novo) ---
            elseif ($validated['tipo_acesso'] === 'responsavel') {
                // Certifique-se de ter criado o arquivo app/Models/Responsavel.php
                Responsavel::create([
                    'id_perfil' => $user->id,
                    'id_instituicao' => $instituicaoId,
                    'cpf' => $validated['cpf'] ?? null,
                    'endereco' => $request->input('endereco') ?? null, // Se o front enviar endereço no futuro
                ]);
            }

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso!',
                'user' => $user
            ], 201);
        });
    }
}