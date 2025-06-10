<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isFoundGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $groupId=$request->groupId;
        $group=Group::find($groupId);
        if(!$group){
            return response([
                'status'=>false,
                'message'=>'group not found'
            ],404);
        }
        $request->attributes->set('group', $group);
        return $next($request);
    }
}
