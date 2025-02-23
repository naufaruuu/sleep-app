<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckManageUsersPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user has the "Manage Users" permission
        if ($request->user() && !$request->user()->hasPermissionTo('Manage Users')) {
            // Redirect or deny access
            return redirect()->route('home')->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
