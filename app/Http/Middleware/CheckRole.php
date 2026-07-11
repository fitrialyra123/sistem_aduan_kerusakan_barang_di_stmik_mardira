<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()) {
        return redirect('login'); 
    }

    $userRole = Auth::user()->role;

    if($userRole !== 'admin' && $userRole !== 'dev') {
        abort(403, 'maaf sepertinya anda tidak memiliki akses untuk melihat halaman ini');
    }

    return $next($request);

}
}