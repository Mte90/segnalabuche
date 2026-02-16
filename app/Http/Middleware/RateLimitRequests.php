<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'rate_limit_' . $ip;

        $maxAttemptsPerMinute = 5;
        $maxAttemptsPer5Minutes = 100;
        $decayMinutes = 1;
        $decay5Minutes = 5;

        $currentAttempts = RateLimiter::increment($key, $decayMinutes * 60);
        $current5MinAttempts = RateLimiter::increment($key . '_5min', $decay5Minutes * 60);

        if ($currentAttempts > $maxAttemptsPerMinute) {
            return response()->json([
                'error' => 'Troppi tentativi. Riprova tra un minuto.',
                'retry_after' => $decayMinutes * 60,
            ], 429);
        }

        if ($current5MinAttempts > $maxAttemptsPer5Minutes) {
            return response()->json([
                'error' => 'Troppi tentativi. Riprova tra 5 minuti.',
                'retry_after' => $decay5Minutes * 60,
            ], 429);
        }

        return $next($request);
    }
}
