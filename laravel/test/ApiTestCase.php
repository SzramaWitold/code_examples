<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class ApiTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeaders([
            'Accept' => 'application/json'
        ]);
    }

    public function createAndBeUser($attr = []): Authenticatable
    {
        /** @var User $user */
        $user = User::factory()->create($attr);

        return Passport::actingAs($user);
    }

    public function assertExistInResponse($array, $needle, $column = 'id')
    {
        $this->assertNotFalse(array_search($needle, array_column($array, $column)));
    }

    public function assertNotExistInResponse($array, $needle, $column = 'id')
    {
        $this->assertFalse(array_search($needle, array_column($array, $column)));
    }
}
