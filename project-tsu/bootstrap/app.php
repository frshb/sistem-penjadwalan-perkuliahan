<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'revalidate' => \App\Http\Middleware\PreventBackHistory::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan muat ulang halaman.'
                ], 419);
            }
            return redirect()->back()->withInput($request->except('_token'))
                             ->with('error', 'Sesi Anda telah kedaluwarsa. Silakan muat ulang halaman atau ulangi aksi Anda.');
        });
    })->create();

