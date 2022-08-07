<?php

namespace App\Traits;

use App\Enum\StatusCode;

trait ApiResponser
{
    protected function successResponse($data, $message = null, $code = StatusCode::HTTP_OK) {
        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code = StatusCode::HTTP_BAD_REQUEST) {
        return response()->json([
            'status' => 0,
            'message' => $message,
            'data' => null
        ], $code);
    }
}
