<?php

namespace App\Http\Controllers\api;

use App\Enum\PlatformConstant;
use App\Enum\StatusCode;
use App\Http\Controllers\ApiController;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlatformController extends ApiController
{

    public function __construct() {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successResponse(Platform::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $rules = [
            'name' => 'required',
        ];

        $postData = Validator::make($request->post(), $rules)->validate();

        if (!in_array($postData['name'], PlatformConstant::allName())) {
            return $this->errorResponse('Invalid platform name');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $platform = Platform::query()->where('id', $id)->first();

        if (!empty($platform)) {
            return $this->successResponse($platform);
        } else {
            return $this->errorResponse('Platform not found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $platform = Platform::query()->where('id', $id)->first();

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->errorResponse(null, StatusCode::HTTP_FORBIDDEN);
    }
}
