<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        // 1️⃣ Get token from header, query, or authenticated user
        $token = $request->header('X-API-TOKEN') ?? $request->query('token');

        if (!$token) {
            return response()->json(['error' => 'API Token missing'], 401);
        }

        // 2️⃣ Retrieve user by token
        $user = User::where('api_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        // 3️⃣ Get the first sale of the user
        $sale = $user->sales()->where('status', 'active')->first();
        if (!$sale) {
            return response()->json([
                'status' => false,
                'message' => 'No active sale found for this user'
            ], 403);
        }

        // 4️⃣ Get domain record (only if sale exists)
        // $host = parse_url($request->header('Origin') ?? '', PHP_URL_HOST) ?? parse_url($request->header('Referer') ?? '', PHP_URL_HOST) ?? $request->header('X-CLIENT-DOMAIN');
        // $domainRecord = $sale->domains()->where('domain_name', 'demo.prodevsltd.xyz')->first();
        // if (!$domainRecord) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Domain not authorized for this API token'
        //     ], 403);
        // }

        // 5️⃣ Domain request limit check
        if ($sale->requests_count > $sale->allowed_requests) {
            return response()->json([
                'status' => false,
                'message' => 'API request limit reached for this package'
            ], 403);
        }
        

        // 6️⃣ Increment total_requests by 1
        $sale->increment('requests_count');

        // 7️⃣ Proceed with the request
        return $next($request);
    }
}
