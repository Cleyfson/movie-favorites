<?php

namespace Tests\Unit\Auth;

use App\Application\UseCases\Auth\UserRegisterUseCase;
use App\Domain\Entities\User;
use Tests\Mocks\UserRepositoryMock;
use Tests\TestCase;

class UserRegisterUseCaseEdgeCasesTest extends TestCase
{
    private UserRepositoryMock $userRepositoryMock;
    private UserRegisterUseCase $userRegisterUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepositoryMock = new UserRepositoryMock();
        $this->userRegisterUseCase = new UserRegisterUseCase($this->userRepositoryMock);
    }

    public function test_register_with_special_characters_in_name(): void
    {
        $user = $this->userRegisterUseCase->execute(
            'José da Silva Ñoño',
            'jose@example.com',
            'password123'
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('José da Silva Ñoño', $user->getName());
    }

    public function test_register_with_long_email(): void
    {
        $longEmail = str_repeat('a', 50) . '@' . str_repeat('b', 50) . '.com';
        
        $user = $this->userRegisterUseCase->execute(
            'Test User',
            $longEmail,
            'password123'
        );

        $this->assertEquals($longEmail, $user->getEmail());
    }

    public function test_register_multiple_users_with_different_emails(): void
    {
        $user1 = $this->userRegisterUseCase->execute(
            'User 1',
            'user1@example.com',
            'password123'
        );

        $user2 = $this->userRegisterUseCase->execute(
            'User 2',
            'user2@example.com',
            'password456'
        );

        $this->assertNotEquals($user1->getId(), $user2->getId());
        $this->assertEquals(2, count($this->userRepositoryMock->getUsers()));
    }

    public function test_register_with_case_sensitive_email(): void
    {
        $this->userRegisterUseCase->execute(
            'User 1',
            'test@example.com',
            'password123'
        );

        $user2 = $this->userRegisterUseCase->execute(
            'User 2',
            'TEST@EXAMPLE.COM',
            'password456'
        );

        $this->assertEquals('TEST@EXAMPLE.COM', $user2->getEmail());
    }

    protected function tearDown(): void
    {
        $this->userRepositoryMock->clear();
        parent::tearDown();
    }
}