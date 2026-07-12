<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePrintAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $branch = Branch::where('print_agent_token', $token)->first();

        if (! $branch) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->attributes->set('print_agent_branch', $branch);

        return $next($request);
    }
}
