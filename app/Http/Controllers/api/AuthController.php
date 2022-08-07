<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {

        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'device_name' => 'required'
        ];

        $postData = Validator::make($request->post(), $rules)->validate();

        /**
         * @var User $user
         */
        $user = User::query()->where('email', $postData['email'])->first();

        if (empty($user) || !Hash::check($postData['password'], $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => ['User not found.']
            ]);
        }

        $token = $user->createToken($postData['device_name'])->plainTextToken;

        return $this->successResponse(["access_token" => $token]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:user',
            'password' => 'required|min:8'
        ];

        $postData = Validator::make($request->post(), $rules)->validate();

        $user = User::query()->create([
            'email' => $postData['email'],
            'password' => Hash::make($postData['password'])
        ]);

        return $this->successResponse(null);
    }
}
