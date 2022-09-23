<?php

namespace App\Http\Controllers\api;

use App\Enum\PlatformConstant;
use App\Enum\StatusCode;
use App\Http\Controllers\ApiController;
use App\Models\Platform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlatformController extends ApiController
{

    public function __construct() {
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

        $query = Platform::query();

        foreach (['type', 'status'] as $field) {
            if (!empty($params[$field])) {
                $query->where($field, $params[$field]);
            }
        }

        $result = $query->get();

        return $this->successResponse($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $postData = $request->post();

        $rules = [
            'name' => 'required',
            'group' => 'required',
            'type' => 'required'
        ];

        Validator::make($postData, $rules)->validate();

        if (!in_array($postData['group'], PlatformConstant::allGroup())) {
            return $this->errorResponse('Invalid platform group');
        }

        if (!in_array($postData['type'], PlatformConstant::allType())) {
            return $this->errorResponse('Invalid platform type');
        }

        if (!isset($postData['status'])) {
            $postData['status'] = PlatformConstant::STATUS_ACTIVE;
        } else {
            if (!in_array($postData['status'], PlatformConstant::allStatus())) {
                return $this->errorResponse('Invalid platform status');
            }
        }

        $platform = new Platform($postData);

        $platform->save();
        return $this->successResponse($platform);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $platform = Platform::query()->find($id);

        if (!empty($platform)) {
            return $this->successResponse($platform);
        } else {
            return $this->errorResponse('Platform not found');
        }
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

        $platform = Platform::query()->find($id);

        if (empty($platform)) {
            return $this->errorResponse('Platform not found');
        } else {
            $platform->update($request->all());
            return $this->successResponse($platform);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return $this->errorResponse(null, StatusCode::HTTP_FORBIDDEN);
    }

}
