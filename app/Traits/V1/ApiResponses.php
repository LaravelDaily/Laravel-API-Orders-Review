<?php

namespace App\Traits\V1;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    public function responseNotAuthorized(string $message = 'You are not authorized.'): JsonResponse
    {
        return $this->responseError($message, Response::HTTP_FORBIDDEN);
    }

    public function responseNotFound(string $message = 'Not Found.'): JsonResponse
    {
        return $this->responseError($message, Response::HTTP_NOT_FOUND);
    }

    protected function responseSuccess(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }

    protected function responseError($message, int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'errors' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}
