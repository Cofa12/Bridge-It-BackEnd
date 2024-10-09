<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizedCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $credentials = [];
        foreach ($request->all() as $item => $value){
            if($item == "email"){
                $credentials [$item] = filter_var($value,FILTER_SANITIZE_EMAIL);
            }else {
                $credentials [$item] = filter_var($value,FILTER_SANITIZE_STRING);
            }
        }
        foreach ($credentials as $item =>$value){
            if($item=="email"){
                $request->email = $value;
            }else if($item=='name'){
                $request->name = $value;
            }else{
                $request->password = $value;
            }
        }

        return $next($request);
    }
}
