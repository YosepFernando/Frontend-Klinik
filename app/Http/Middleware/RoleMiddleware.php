<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated via API session
        if (!is_authenticated()) {
            \Log::warning('RoleMiddleware: User not authenticated', [
                'url' => $request->url(),
                'method' => $request->method()
            ]);
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        $user = auth_user();
        
        if (!$user) {
            \Log::warning('RoleMiddleware: User object not found', [
                'url' => $request->url(),
                'method' => $request->method()
            ]);
            return redirect()->route('login')->with('error', 'Data pengguna tidak ditemukan. Silakan login kembali.');
        }
        
        \Log::info('RoleMiddleware: Checking user role', [
            'user_role' => $user->role ?? 'no role',
            'required_roles' => $roles,
            'url' => $request->url(),
            'user_id' => $user->id ?? 'no id'
        ]);
        
        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            \Log::warning('RoleMiddleware: Access denied', [
                'user_role' => $user->role ?? 'no role',
                'required_roles' => $roles,
                'url' => $request->url()
            ]);
            abort(403, 'Unauthorized access. Required roles: ' . implode(', ', $roles) . '. Your role: ' . ($user->role ?? 'none'));
        }

        return $next($request);
    }
}
