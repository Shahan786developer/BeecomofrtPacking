<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $module
     * @param  string  $action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'read'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['login' => 'Your account has been deactivated. Please contact Super Admin.']);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Support multiple actions piped together (e.g. read|create)
        $actions = explode('|', $action);
        $hasAnyAction = false;
        foreach ($actions as $act) {
            if ($user->hasPermission($module, trim($act))) {
                $hasAnyAction = true;
                break;
            }
        }

        if (!$hasAnyAction) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => "You do not have permission to access this feature ({$module} : {$action}).",
                ], 403);
            }

            abort(403, "Access Denied: You do not have permission to {$action} in {$module}. Please contact Super Admin.");
        }

        return $next($request);
    }
}
