<?php

namespace App\Http\Controllers\api;

use App\Enum\StatusCode;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class TaskController extends ApiController {

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @return JsonResponse
     */
    public function listLog() {
        $logFile = file(storage_path(). '/logs/task.log');

        $logObjects = [];
        foreach ($logFile as $index => $line) {
            $logObjects[] = [
                'line' => $index + 1,
                'content' => htmlspecialchars($line)
            ];
        }
        return $this->successResponse($logObjects);
    }

    /**
     * @return JsonResponse
     */
    public function deleteLog() {
        $result = file_put_contents(storage_path() . '/logs/task.log', '');

        if ($result === false) {
            return $this->errorResponse('cannot remove log');
        } else {
            return $this->successResponse([], 'removed');
        }
    }
}
