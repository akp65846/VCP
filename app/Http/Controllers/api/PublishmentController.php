<?php

namespace App\Http\Controllers\api;

use App\Enum\PublishmentConstant;
use App\Enum\StatusCode;
use App\Enum\VideoConstant;
use App\Http\Controllers\ApiController;
use App\Models\Publishment;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublishmentController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @throws ValidationException
     */
    public function publish(Request $request): JsonResponse
    {
        $postData = $request->post();

        $rules = [
            'video_id' => 'required',
            'target_platform_id' => 'required|exists:platform,id',
        ];

        Validator::make($postData, $rules)->validate();

        $video = Video::query()->find($postData['video_id']);

        if (!$video instanceof Video) {
            return $this->errorResponse("Video not found");
        }

        if ($video['approval_status'] != VideoConstant::APPROVAL_STATUS_APPROVED) {
            return $this->errorResponse("Video not approved");
        }

        $publishment = new Publishment([
            'source_platform_id' => $video['platform_id'],
            'target_platform_id' => $postData['target_platform_id'],
            'video_id' => $video['id'],
            'scheduled_time' => empty($postData['scheduled_time']) ? now() : date('Y-m-d H:i:s', $postData['scheduled_time']),
            'status' => PublishmentConstant::STATUS_ACTIVE
        ]);

        $publishment->save();

        return $this->successResponse($publishment, null, StatusCode::HTTP_CREATED);
    }
}
