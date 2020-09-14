<?php

namespace App\Http\Middleware;

use Closure;

class AddToken
{
    public function handle($request, Closure $next)
    {
        $input = $request->input();
        // return response($input['tk'], 200);

        if(!empty($input['tk'])){
            $request->headers->set('Authorization','Bearer '.$input['tk']);
        }
        return $next($request);
    }
}
