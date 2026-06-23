<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        // Админ может всё
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Если email не подтверждён — редирект на страницу подтверждения
        if ($request->user() && !$request->user()->email_verified_at) {
            // Разрешаем доступ только к страницам подтверждения и выходу
            if (!$request->routeIs('profile.verify-email*') && !$request->routeIs('logout')) {
                return redirect()->route('profile.verify-email')
                    ->with('warning', 'Подтвердите ваш email для продолжения работы');
            }
        }

        return $next($request);
    }
}