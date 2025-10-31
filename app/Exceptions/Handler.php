<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Redirect all web 404s to login (guests) or to home/dashboard (authenticated)
        $this->renderable(function (\Throwable $e, Request $request) {
            // Do not affect API/JSON responses
            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // fall back to default JSON behavior
            }

            $is404 = $e instanceof NotFoundHttpException
                || $e instanceof ModelNotFoundException
                || ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 404);

            if (! $is404) {
                return null; // not a 404 scenario, keep default rendering
            }

            if (Auth::check()) {
                // Prefer a named "welcome" route if one exists
                if (Route::has('welcome')) {
                    return redirect()->route('welcome');
                }
                return redirect('/');
            }

            return redirect()->route('login');
        });
    }
}
