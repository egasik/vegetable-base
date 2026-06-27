<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Доступ запрещен');
        }

        return $next($request);
    }
}