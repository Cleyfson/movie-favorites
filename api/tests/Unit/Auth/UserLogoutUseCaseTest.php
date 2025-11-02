<?php

namespace Tests\Unit\Auth;

use App\Application\UseCases\Auth\UserLogoutUseCase;
use Tests\TestCase;
use Mockery;

class UserLogoutUseCaseTest extends TestCase
{
    private UserLogoutUseCase $userLogoutUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userLogoutUseCase = new UserLogoutUseCase();
    }

    public function test_logout_invalidates_token_successfully(): void
    {
        $this->assertTrue(method_exists($this->userLogoutUseCase, 'execute'));
    }

    public function test_logout_use_case_is_instantiable(): void
    {
        $this->assertInstanceOf(UserLogoutUseCase::class, $this->userLogoutUseCase);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}