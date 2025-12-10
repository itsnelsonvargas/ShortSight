<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            // Log all exceptions for monitoring
            \Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): \Symfony\Component\HttpFoundation\Response
    {
        // Handle API requests differently from web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return $this->handleWebException($request, $e);
    }

    /**
     * Handle API exceptions with JSON responses.
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'type' => 'validation_error'
            ], 422);
        }

        // Not found errors
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found',
                'type' => 'not_found'
            ], 404);
        }

        // HTTP exceptions
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $this->getHttpExceptionMessage($statusCode);

            return response()->json([
                'success' => false,
                'message' => $message,
                'type' => 'http_error',
                'status_code' => $statusCode
            ], $statusCode);
        }

        // Generic server errors
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again later.',
            'type' => 'server_error',
            'error_id' => $this->generateErrorId()
        ], 500);
    }

    /**
     * Handle web exceptions with HTML responses.
     */
    protected function handleWebException(Request $request, Throwable $e)
    {
        // Validation errors - redirect back with errors
        if ($e instanceof ValidationException) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Not found errors - show custom 404 page
        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [
                'message' => 'The page you\'re looking for doesn\'t exist.',
                'title' => 'Page Not Found'
            ], 404);
        }

        // For link redirects, show a user-friendly error page
        if ($request->route() && $request->route()->getName() === 'link.show') {
            return response()->view('errors.link-error', [
                'message' => 'We couldn\'t redirect you to this link. It may have been removed or is temporarily unavailable.',
                'title' => 'Link Unavailable',
                'slug' => $request->route('slug') ?? ''
            ], 404);
        }

        // Generic server errors - show custom error page
        return response()->view('errors.500', [
            'message' => 'Something went wrong on our end. Please try again later.',
            'title' => 'Server Error',
            'error_id' => $this->generateErrorId()
        ], 500);
    }

    /**
     * Get user-friendly messages for HTTP status codes.
     */
    protected function getHttpExceptionMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad request. Please check your input and try again.',
            401 => 'Authentication required. Please log in to continue.',
            403 => 'You don\'t have permission to access this resource.',
            404 => 'The requested resource was not found.',
            405 => 'This method is not allowed for this resource.',
            408 => 'The request took too long to process. Please try again.',
            413 => 'The request is too large to process.',
            414 => 'The request URL is too long.',
            415 => 'The request format is not supported.',
            422 => 'The request contains invalid data.',
            429 => 'Too many requests. Please slow down and try again later.',
            500 => 'An internal server error occurred. Please try again later.',
            502 => 'Bad gateway. The service is temporarily unavailable.',
            503 => 'Service unavailable. Please try again later.',
            504 => 'Gateway timeout. Please try again later.',
            default => 'An error occurred. Please try again later.',
        };
    }

    /**
     * Generate a unique error ID for tracking.
     */
    protected function generateErrorId(): string
    {
        return 'ERR-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
}
