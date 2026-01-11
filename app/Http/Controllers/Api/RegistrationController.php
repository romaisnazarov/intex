<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nickname' => 'required|string|max:255',
            'avatar' => 'required|base64image|base64mimetypes:image/jpeg,image/png,image/gif|base64max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $nickname = $request->input('nickname');
        $avatar = $request->input('avatar');

        $nicknameKey = "user:nickname:{$nickname}";
        if (Redis::exists($nicknameKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Данный nickname уже существует'
            ], 409);
        }

        try {
            preg_match('/^data:image\/(\w+);base64,/', $avatar, $matches);
            $imageType = $matches[1];
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $avatar));

            $userId = Str::uuid()->toString();
            $avatarFileName = "avatars/{$userId}.{$imageType}";

            Storage::disk('public')->put($avatarFileName, $imageData);

            $userData = [
                'id' => $userId,
                'nickname' => $nickname,
                'avatar' => $avatarFileName,
                'created_at' => now()->toIso8601String(),
            ];

            $userKey = "user:{$userId}";
            Redis::setex($userKey, 86400, json_encode($userData)); // 24 hours

            Redis::setex($nicknameKey, 86400, $userId);

            $usersListKey = "users:list";
            Redis::zadd($usersListKey, now()->timestamp, $userId);

            $avatarUrl = Storage::url($avatarFileName);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован',
                'data' => [
                    'id' => $userId,
                    'nickname' => $nickname,
                    'avatar_url' => $avatarUrl,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

