<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $user_id = Auth::id() ;
        $value=DB::table('group_user')
            ->where('group_id',$request->input('groupId'))
            ->where('user_id',$user_id)
            ->where('position','admin')->exists();

        return $value?$next($request):
            response()->json([
                'status'=>false,
                'message'=>'You are not authorized to access this page',
        ],401);


    }
}
