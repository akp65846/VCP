<?php

namespace App\Http\Controllers\api;

use App\Enum\StatusCode;
use App\Enum\VideoConstant;
use App\Http\Controllers\ApiController;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->successResponse(Video::all());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function approveVideo(Request $request): JsonResponse
    {
        $postData = $request->post();

        if (empty($postData['video_id'])) {
            $this->errorResponse("Missing Video ID");
        }

        $video = Video::query()->find($postData['video_id']);
        if (!$video instanceof Video) {
            $this->errorResponse("Video not found");
        }

        if ($this->approve($video)) {
            return $this->successResponse($video->refresh());
        } else {
            return $this->errorResponse('Cannot process request', StatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function rejectVideo(Request $request): JsonResponse
    {
        $postData = $request->post();

        if (empty($postData['video_id'])) {
            $this->errorResponse("Missing Video ID");
        }

        $video = Video::query()->find($postData['video_id']);
        if (!$video instanceof Video) {
            $this->errorResponse("Video not found");
        }

        if ($this->reject($video)) {
            return $this->successResponse($video->refresh());
        } else {
            return $this->errorResponse('Cannot process request', StatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Approve a video
     *
     * @param Video $video
     * @return bool
     */
    private function approve(Video $video): bool
    {
        if (intval($video['approval_status']) !== VideoConstant::APPROVAL_STATUS_PENDING) {
            return FALSE;
        }

        $video->update([
            'approval_status' => VideoConstant::APPROVAL_STATUS_APPROVED,
            'approval_time' => now(),
        ]);

        return TRUE;
    }

    private function reject(Video $video): bool
    {
        if (intval($video['approval_status']) !== VideoConstant::APPROVAL_STATUS_PENDING) {
            return FALSE;
        }

        $video->update([
            'approval_status' => VideoConstant::APPROVAL_STATUS_REJECTED,
            'approval_time' => now(),
        ]);

        return TRUE;
    }
}
