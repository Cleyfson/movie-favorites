<?php

namespace Tests\Unit\Infra\Repositories;

use App\Infra\Repositories\UserRepository;
use App\Domain\Entities\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Mockery;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function test_find_by_email_returns_user_when_found(): void
    {
        $email = 'john@example.com';
        $userData = (object) [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password'
        ];

        DB::shouldReceive('table')->with('users')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('email', $email) ->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn($userData);

        $result = $this->repository->findByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
        $this->assertEquals('hashed_password', $result->getPassword());
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $email = 'nonexistent@example.com';

        DB::shouldReceive('table')->with('users')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('email', $email)->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn(null);

        $result = $this->repository->findByEmail($email);

        $this->assertNull($result);
    }

    public function test_save_inserts_user_into_database(): void
    {
        $user = (new User())
            ->setName('Jane Doe')
            ->setEmail('jane@example.com')
            ->setPassword('hashed_password');

        DB::shouldReceive('table')->with('users')->once()->andReturnSelf();
        DB::shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['name'] === 'Jane Doe' &&
                       $data['email'] === 'jane@example.com' &&
                       $data['password'] === 'hashed_password' &&
                       isset($data['created_at']) &&
                       isset($data['updated_at']);
            }));

        $this->repository->save($user);        $this->assertTrue(true);
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(\App\Domain\Repositories\UserRepositoryInterface::class, $this->repository);
    }

    public function test_find_by_email_with_special_characters(): void
    {
        $email = 'user+tag@example-domain.com';
        $userData = (object) [
            'id' => 2,
            'name' => 'Special User',
            'email' => $email,
            'password' => 'password'
        ];

        DB::shouldReceive('table')->with('users')->once()->andReturnSelf();
        DB::shouldReceive('where')->with('email', $email)->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn($userData);

        $result = $this->repository->findByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}