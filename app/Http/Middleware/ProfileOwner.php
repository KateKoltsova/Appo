<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileOwner
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->id != $request->route('user')) {
            return response()->json(['message' => 'You don\'t have permissions to make this action'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
