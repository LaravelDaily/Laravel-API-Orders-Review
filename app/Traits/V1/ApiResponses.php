<?php

namespace App\Traits\V1;

trait ApiResponses
{
    protected function ok(string $message, array $data = [])
    {
        return $this->success($message, $data, 200);
    }

    protected function success(string $message, array $data = [], int $statusCode = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }

    protected function error($message, $statusCode)
    {
        return response()->json(['errors' => $message, 'status' => $statusCode], $statusCode);
    }
}
