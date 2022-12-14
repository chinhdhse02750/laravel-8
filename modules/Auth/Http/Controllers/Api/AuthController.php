<?php

namespace Modules\Auth\Http\Controllers\Api;

use Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response([
                'status' => 'error',
                'error'  => 'invalid.credentials',
                'msg'    => 'Invalid Credentials.'
            ], 400);
        }

        $userData = auth('api')->user();

        return response([
            'status' => 'success',
            'token'  => $token,
            'roles'    => [
                $userData->role->role_name
            ]
        ])->header('Authorization', $token);
    }

    public function logout(Request $request)
    {
        try {
            /** @phpstan-ignore-next-line */
            $token = JWTAuth::getToken();
            /** @phpstan-ignore-next-line */
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], 401);
        }

        return response([
            'status' => 'success'
        ]);
    }

    public function refresh()
    {
        try {
            /** @phpstan-ignore-next-line */
           $token = JWTAuth::getToken();
            /** @phpstan-ignore-next-line */
           if (!$user = JWTAuth::parseToken()->authenticate()) {
               return response()->json(['user_not_found'], 404);
           }
        } catch (TokenExpiredException $e) {
           return response()->json(['token_expired'], 401);
        } catch (TokenBlacklistedException $e) {
           return response()->json(['token_expired'], 401);
        } catch (\Exception $e) {
           return response()->json(['token_expired'], 401);
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function user()
    {
        $userData = auth('api')->user();

        return response(
            [
                'status' => 'success',
                'data'   => [
                    'email'    => $userData->email,
                    'name'     => $userData->name,
                    'roles'    => [
                        $userData->role->role_name
                    ]
                ]
            ]
        );
    }
}
