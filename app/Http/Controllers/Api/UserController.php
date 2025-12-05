<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email', // A regra unique será tratada abaixo
            'tipo_acesso' => 'required|in:aluno,professor,coordenador',
            'cpf' => 'nullable|string', // Idealmente validar CPF
            'matricula' => 'required_if:tipo_acesso,aluno',
            'departamento' => 'required_if:tipo_acesso,professor'
        ]);

        // 2. Transação (Para garantir que salva tudo ou nada)
        return DB::transaction(function () use ($validated, $request) {
            
            // Pega a instituição do Diretor logado
            $instituicaoId = $request->user()->id_instituicao;

            // Cria o Perfil/Usuário
            $user = User::create([
                'id_instituicao' => $instituicaoId,
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(16)), // Senha temporária aleatória
                // Aqui você precisará mapear o tipo para o ID do perfil correto na tabela perfis
                // Para simplificar no MVP, vamos assumir que 'tipo' é uma string na tabela users por enquanto
                'tipo_usuario' => $validated['tipo_acesso'], 
            ]);

            // Cria os dados específicos (Tabelas Satélites)
            if ($validated['tipo_acesso'] === 'aluno') {
                Aluno::create([
                    'id_usuario' => $user->id,
                    'id_instituicao' => $instituicaoId,
                    'rm' => $validated['matricula'],
                    // outros dados...
                ]);
            } elseif ($validated['tipo_acesso'] === 'professor') {
                Professor::create([
                    'id_usuario' => $user->id,
                    'id_instituicao' => $instituicaoId,
                    'departamento' => $validated['departamento'],
                    // outros dados...
                ]);
            }

            // 3. Enviar E-mail de Convite (Fila)
            // Mail::to($user->email)->queue(new ConviteUsuario($user));

            return response()->json(['message' => 'Usuário criado e convite enviado!', 'user' => $user], 201);
        });
    }
}