<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLokasiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Jika user adalah admin, lewati pengecekan
        if ($user && $user->isAdmin()) {
            return $next($request);
        }
        
        // Jika user adalah petugas dan tidak memiliki lokasi_id
        if ($user && $user->isPetugas() && !$user->lokasi_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke lokasi manapun. Hubungi administrator.');
        }
        
        return $next($request);
    }
}