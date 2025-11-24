<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Tentar login
        if (!Auth::attempt($credenciais)) {
            return response()->json([
                'mensagem' => 'Credenciais inválidas.',
            ], 401);
        }

        // 3. Recuperar usuário autenticado
        $user = Auth::user();

        // 4. Criar token do Sanctum
        $token = $user->createToken('login_token')->plainTextToken;

        // 5. Retornar tudo organizado
        return response()->json([
            'mensagem' => 'Login realizado com sucesso!',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
