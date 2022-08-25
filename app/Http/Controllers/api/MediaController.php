<?php

namespace App\Http\Controllers\api;

use App\Enum\StatusCode;
use App\Http\Controllers\ApiController;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends ApiController
{

    public function __construct() {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->successResponse(Media::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $media = Media::query()->find($id);

        if (empty($media)) {
            return $this->errorResponse("Media not found");
        }

        return $this->successResponse($media);
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

        $media = Media::query()->find($id);

        if (empty($media)) {
            return $this->errorResponse("Media not found");
        }

        $media->update($patchData);

        return $this->successResponse($media);
    }
}
