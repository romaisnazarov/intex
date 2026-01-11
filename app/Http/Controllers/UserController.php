<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $usersListKey = "users:list";
        $userIds = Redis::zrange($usersListKey, 0, -1);

        $users = [];
        foreach ($userIds as $userId) {
            $userKey = "user:{$userId}";
            $userData = Redis::get($userKey);

            if ($userData) {
                $user = json_decode($userData, true);

                if (isset($user['avatar']) && Storage::disk('public')->exists($user['avatar'])) {
                    $user['avatar_url'] = asset('storage/' . $user['avatar']);
                } else {
                    $user['avatar_url'] = null;
                }
                $users[] = $user;
            }
        }

        return view('users.index', compact('users'));
    }
}

