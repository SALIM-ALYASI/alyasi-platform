<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * التحقق من تسجيل دخول المدير وحالة الحساب.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $guard = Auth::guard('admin');

        if (! $guard->check()) {
            return redirect()
                ->route('admin.login')
                ->with(
                    'error',
                    'يرجى تسجيل الدخول للوصول إلى لوحة التحكم.'
                );
        }

        $admin = $guard->user();

        if (! $admin || ! $admin->is_active) {
            $guard->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with(
                    'error',
                    'هذا الحساب غير نشط.'
                );
        }

        return $next($request);
    }
}