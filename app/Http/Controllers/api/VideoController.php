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
    public function index(Request $request)
    {
        $params = $request->query();

        $query = Video::query();

        foreach (['approval_status', 'status'] as $field) {
            if (!empty($params[$field])) {
                $query->where($field, $params[$field]);
            }
        }

        if (!empty($params['order_by'])) {
            if (!empty($params['order'])) {
                $order = 'desc';
            } else {
                $order = 'asc';
            }

            $query->orderBy($params['order_by'], $order);
        }

        return $this->successResponse($query->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $video = Video::query()->find($id);

        if (empty($video)) {
            return $this->errorResponse("Video not found");
        }

        return $this->successResponse($video);
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

        if ($this->approve($video, $postData['remarks'])) {
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

        if ($this->reject($video, $postData['remarks'])) {
            return $this->successResponse($video->refresh());
        } else {
            return $this->errorResponse('Cannot process request', StatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $video = Video::query()->find($id);

        $patchData = $request->all();

        unset($patchData['approval_status'], $patchData['approval_time']);

        if (!$video instanceof Video) {
            return $this->errorResponse('Video not found');
        }

        $video->update($patchData);
        return $this->successResponse($video);
    }


    /**
     * Approve a video
     *
     * @param Video $video
     * @param $remarks
     * @return bool
     */
    private function approve(Video $video, $remarks = null): bool
    {
        if (intval($video['approval_status']) !== VideoConstant::APPROVAL_STATUS_PENDING) {
            return FALSE;
        }

        $video->update([
            'approval_status' => VideoConstant::APPROVAL_STATUS_APPROVED,
            'approval_time' => now(),
            'remarks' => $remarks
        ]);

        return TRUE;
    }

    private function reject(Video $video, $remarks = null): bool
    {
        if (intval($video['approval_status']) !== VideoConstant::APPROVAL_STATUS_PENDING) {
            return FALSE;
        }

        $video->update([
            'approval_status' => VideoConstant::APPROVAL_STATUS_REJECTED,
            'approval_time' => now(),
            'remarks' => $remarks
        ]);

        return TRUE;
    }
}
