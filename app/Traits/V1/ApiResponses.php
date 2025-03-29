<?php

namespace App\Traits\V1;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    public function notAuthorized(string $message = 'You are not authorized.'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    public function notFound(string $message = 'Not Found.'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    public function unexpectedError(string $message = 'An unexpected error occurred.'): JsonResponse
    {
        return $this->error($message);
    }

    public function dbError(string $message = 'Database error.'): JsonResponse
    {
        return $this->error($message);
    }

    protected function success(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }

    protected function error($message, $statusCode = 500): JsonResponse
    {
        return response()->json([
            'errors' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}
