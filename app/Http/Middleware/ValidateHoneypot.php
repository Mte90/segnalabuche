<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateHoneypot
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('website') && !empty($request->input('website'))) {
            return response()->json([
                'error' => 'Rilevato spam. La richiesta è stata bloccata.',
            ], 403);
        }

        return $next($request);
    }
}
