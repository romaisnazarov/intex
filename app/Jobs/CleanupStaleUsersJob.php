<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupStaleUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Default stale time in minutes
     */
    private const DEFAULT_STALE_MINUTES = 1;

    protected int $staleMinutes;

    /**
     * @param int $staleMinutes
     */
    public function __construct(int $staleMinutes = self::DEFAULT_STALE_MINUTES)
    {
        $this->staleMinutes = $staleMinutes;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $staleTimestamp = now()->subMinutes($this->staleMinutes)->timestamp;
        $usersListKey = "users:list";

        $userIds = Redis::zrangebyscore($usersListKey, 0, $staleTimestamp);

        $deletedCount = 0;

        foreach ($userIds as $userId) {
            try {
                $userKey = "user:{$userId}";
                $userData = Redis::get($userKey);

                if ($userData) {
                    $user = json_decode($userData, true);

                    if (isset($user['avatar']) && Storage::disk('public')->exists($user['avatar'])) {
                        Storage::disk('public')->delete($user['avatar']);
                    }

                    if (isset($user['nickname'])) {
                        $nicknameKey = "user:nickname:{$user['nickname']}";
                        Redis::del($nicknameKey);
                    }

                    Redis::del($userKey);
                }

                Redis::zrem($usersListKey, $userId);

                $deletedCount++;

            } catch (\Exception $e) {
                Log::error("Ошибка при удалении пользователя {$userId}: " . $e->getMessage());
            }
        }

        Log::info("Очистка прошла успешно. Удалено {$deletedCount} устаревших пользователей старше {$this->staleMinutes} минут.");
    }
}

