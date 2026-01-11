<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushAll();
        Storage::fake('public');
    }

    /**
     * @return void
     */
    public function test_successful_registration(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'nickname',
                    'avatar_url',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertEquals('testuser', $responseData['nickname']);
        $this->assertNotEmpty($responseData['id']);
        $this->assertNotEmpty($responseData['avatar_url']);
    }

    /**
     * @return void
     */
    public function test_registration_fails_without_nickname(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson('/api/register', [
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['nickname']);
    }

    /**
     * @return void
     */
    public function test_registration_fails_without_avatar(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * @return void
     */
    public function test_registration_fails_with_duplicate_nickname(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => $base64Image,
        ]);

        $response = $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Такой nickname уже существует',
            ]);
    }

    /**
     * @return void
     */
    public function test_registration_succeeds_with_jpeg_image(): void
    {
        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A';

        $response = $this->postJson('/api/register', [
            'nickname' => 'jpeguser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @return void
     */
    public function test_registration_succeeds_with_gif_image(): void
    {
        $base64Image = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

        $response = $this->postJson('/api/register', [
            'nickname' => 'gifuser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @return void
     */
    public function test_registration_fails_with_nickname_too_long(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $longNickname = str_repeat('a', 256);

        $response = $this->postJson('/api/register', [
            'nickname' => $longNickname,
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['nickname']);
    }

    /**
     * @return void
     */
    public function test_avatar_is_stored_in_storage(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson('/api/register', [
            'nickname' => 'storageuser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(201);

        $userId = $response->json('data.id');
        $this->assertNotNull($userId);

        Storage::disk('public')->assertExists("avatars/{$userId}.png");
    }

    /**
     * @return void
     */
    public function test_user_data_is_stored_in_redis(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson('/api/register', [
            'nickname' => 'redisuser',
            'avatar' => $base64Image,
        ]);

        $response->assertStatus(201);

        $userId = $response->json('data.id');
        $this->assertNotNull($userId);

        $userKey = "user:{$userId}";
        $this->assertGreaterThan(0, Redis::exists($userKey));

        $userData = json_decode(Redis::get($userKey), true);
        $this->assertEquals('redisuser', $userData['nickname']);
        $this->assertEquals($userId, $userData['id']);
        $this->assertArrayHasKey('avatar', $userData);
        $this->assertArrayHasKey('created_at', $userData);

        $nicknameKey = "user:nickname:redisuser";
        $this->assertGreaterThan(0, Redis::exists($nicknameKey));
        $this->assertEquals($userId, Redis::get($nicknameKey));
    }
}

