<?php

use App\Exceptions\FinancialImmutableException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\LoanWorkflowException;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureIdempotencyKey;
use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'idempotency' => EnsureIdempotencyKey::class,
            'admin.role' => EnsureAdminRole::class,
        ]);

        // Satu-satunya guard sesi di aplikasi ini adalah portal Web Admin.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (InsufficientBalanceException $e, Request $request) => $request->is('api/*')
            ? ApiResponse::error(
                message: $e->getMessage(),
                code: 'INSUFFICIENT_BALANCE',
                status: Response::HTTP_BAD_REQUEST,
            )
            : back()->withErrors(['error' => $e->getMessage()])->withInput());

        $exceptions->render(fn (FinancialImmutableException $e, Request $request) => $request->is('api/*')
            ? ApiResponse::error(
                message: $e->getMessage(),
                code: 'FINANCIAL_RECORD_IMMUTABLE',
                status: Response::HTTP_CONFLICT,
            )
            : back()->withErrors(['error' => $e->getMessage()]));

        $exceptions->render(fn (LoanWorkflowException $e, Request $request) => $request->is('api/*')
            ? ApiResponse::error(
                message: $e->getMessage(),
                code: 'LOAN_WORKFLOW_VIOLATION',
                status: Response::HTTP_BAD_REQUEST,
            )
            : back()->withErrors(['error' => $e->getMessage()])->withInput());
    })->create();
