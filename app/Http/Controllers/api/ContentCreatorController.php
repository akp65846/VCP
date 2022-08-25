<?php

namespace App\Http\Controllers\api;

use App\Enum\ContentCreatorConstant;
use App\Enum\StatusCode;
use App\Http\Controllers\ApiController;
use App\Models\ContentCreator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ContentCreatorController extends ApiController
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
        return $this->successResponse(ContentCreator::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'platform_id' => 'required|exists:platform,id',
            'status' => 'required',
        ];

        $postData = $request->post();
        Validator::make($postData, $rules)->validate();

        if (!in_array($postData['status'], ContentCreatorConstant::allStatus())) {
            return $this->errorResponse('Invalid status');
        }

        $contentCreator = new ContentCreator($request->post());
        $contentCreator->save();

        $contentCreator = $contentCreator->refresh();

        return $this->successResponse($contentCreator, StatusCode::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $contentCreator = ContentCreator::query()->find($id);

        if (empty($contentCreator)) {
            return $this->errorResponse("Content creator not found");
        }

        return $this->successResponse($contentCreator);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $patchData = $request->post();

        $contentCreator = ContentCreator::query()->find($id);

        if (empty($contentCreator)) {
            return $this->errorResponse("Content creator not found");
        }

        $contentCreator->update($patchData);
        return $this->successResponse($contentCreator);
    }
}
