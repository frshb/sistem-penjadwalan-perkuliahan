<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // If no roles passed, just check auth (already done above)
        if (empty($roles)) {
            return $next($request);
        }

        // Map role names to IDs or use helper if roles are passed as strings
        // Implementation Plan used strings like 'admin', 'dekan'.
        // User model has helpers but we need to match against the user's role name.

        $userRoleName = $user->role->nama_role;

        // --- FIX: Map Legacy Roles to New Roles ---
        // DB has 'super_admin' and 'admin_fakultas', code expects 'admin'.
        if ($userRoleName === 'super_admin' || $userRoleName === 'admin_fakultas') {
            $userRoleName = 'admin';
        }
        // ------------------------------------------

        // \Illuminate\Support\Facades\Log::info('CheckRole Debug: User ' . $user->username . ' has role: ' . $userRoleName . '. Required roles: ' . implode(',', $roles));

        if (in_array($userRoleName, $roles)) {
            return $next($request);
        }

        // Allow Admin to access everything? (Optional, but "Admin full akses" was requested)
        if ($userRoleName === 'admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
