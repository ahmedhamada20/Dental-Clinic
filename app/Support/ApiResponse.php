<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    /**
     * Return success response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $extra = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json(
            array_merge($response, $extra),
            $statusCode
        );
    }

    /**
     * Return paginated response
     */
    public static function paginated(
        AbstractPaginator $paginator,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $statusCode);
    }

    /**
     * Return error response
     */
    public static function error(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null,
        array $extra = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json(
            array_merge($response, $extra),
            $statusCode
        );
    }

    /**
     * Return validation error response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed',
        int $statusCode = 422
    ): JsonResponse {
        return self::error(
            $message,
            $statusCode,
            $errors
        );
    }

    /**
     * Return unauthorized response
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        int $statusCode = 401
    ): JsonResponse {
        return self::error($message, $statusCode);
    }

    /**
     * Return forbidden response
     */
    public static function forbidden(
        string $message = 'Forbidden',
        int $statusCode = 403
    ): JsonResponse {
        return self::error($message, $statusCode);
    }

    /**
     * Return not found response
     */
    public static function notFound(
        string $message = 'Resource not found',
        int $statusCode = 404
    ): JsonResponse {
        return self::error($message, $statusCode);
    }

    /**
     * Return server error response
     */
    public static function serverError(
        string $message = 'Internal server error',
        int $statusCode = 500
    ): JsonResponse {
        return self::error($message, $statusCode);
    }
}

