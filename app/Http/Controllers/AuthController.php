<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validação
        $credenciais = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        // Tenta autenticar
        if (!Auth::attempt($credenciais)) {
            return response()->json([
                'mensagem' => 'Credenciais incorretas.'
            ], 401);
        }

        // Auth funcionou
        $user = Auth::user();

        // Cria token Sanctum
        $token = $user->createToken("token_sam")->plainTextToken;

        return response()->json([
            'mensagem' => 'Login realizado com sucesso!',
            'token' => $token,
            'user' => $user
        ], 200);
    }
}
