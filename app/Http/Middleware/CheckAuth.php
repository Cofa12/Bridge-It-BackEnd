<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if(!Auth::check()){
            return response()->json([
                'message'=>"You haven\'t register yet , You muse register first"
            ],\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $next($request);
    }
}
