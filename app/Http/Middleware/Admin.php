<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Admin
{
    public function handle($request, Closure $next)
    {  
        if($request->user()->role=='admin'){
            return $next($request);
        }
        else{
            request()->session()->flash('error','您沒有任何權限存取該頁面');
            Auth::logout();
            return redirect()->route('login');
        }
    }
}
