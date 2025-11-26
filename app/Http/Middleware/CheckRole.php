<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Verifica se o usuário está logado (Sanctum já faz isso, mas é bom garantir)
        if (! $request->user() || ! $request->user()->perfil) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        // 2. Pega o tipo do usuário no banco (ex: 'diretor')
        // Nota: Precisamos garantir que o Model User carregue a relação 'perfil'
        $userRole = $request->user()->perfil->tipo;

        // 3. Verifica se o tipo do usuário está na lista de permitidos
        // Ex: Se a rota pede 'diretor' e o user é 'aluno', entra no IF.
        if (! in_array($userRole, $roles)) {
            return response()->json(['message' => 'Acesso não autorizado para seu perfil.'], 403);
        }

        return $next($request);
    }
}