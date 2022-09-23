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
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->successResponse(Publishment::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $publishment = Publishment::query()->find($id);

        if (empty($publishment)) {
            return $this->errorResponse('Publishment not found');
        }

        return $this->successResponse($publishment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {

        $patchData = $request->post();

        $publishment = Publishment::query()->find($id);

        if (empty($publishment)) {
            return $this->errorResponse('Publishment not found');
        }

        unset($patchData['upload_trial_time'], $patchData['media_id']);

        $publishment->update($patchData);
        return $this->successResponse($publishment);
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
            'title' => 'required'
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
            'scheduled_time' => empty($postData['scheduled_time']) ? now() : $postData['scheduled_time'],
//            'scheduled_time' => empty($postData['scheduled_time']) ? now() : date('Y-m-d H:i:s', $postData['scheduled_time']),
            'status' => PublishmentConstant::STATUS_ACTIVE,
            'title' => $postData['title'],
            'is_notify_subscribers' => !empty($postData['is_notify_subscribers']) && $postData['is_notify_subscribers'] == 1 ? 1 : 0,
            'description' => empty($postData['description']) ? NULL : $postData['description']
        ]);

        if (!empty($video['media_id'])) {
            $publishment['media_id'] = $video['media_id'];
        }

        $video['publishment_count'] = $video['publishment_count'] + 1;

        $video->save();
        $publishment->save();

        return $this->successResponse($publishment, null, StatusCode::HTTP_CREATED);
    }
}
