<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $module
     * @param  string|null  $item
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $module, $item = null)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Admin has all permissions by default
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasPermission($module, $item)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
