<?php

namespace Tests\Feature\Repositories;

use App\Infra\Repositories\UserRepository;
use App\Domain\Entities\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function test_find_by_email_returns_user_when_found(): void
    {
        $user = (new User())
            ->setName('John Doe')
            ->setEmail('john@example.com')
            ->setPassword('hashed_password');

        $this->repository->save($user);

        $result = $this->repository->findByEmail('john@example.com');

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
        $this->assertEquals('hashed_password', $result->getPassword());
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($result);
    }

    public function test_save_inserts_user_into_database(): void
    {
        $user = (new User())
            ->setName('Jane Doe')
            ->setEmail('jane@example.com')
            ->setPassword('hashed_password');

        $this->repository->save($user);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'hashed_password',
        ]);
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(\App\Domain\Repositories\UserRepositoryInterface::class, $this->repository);
    }

    public function test_find_by_email_with_special_characters(): void
    {
        $email = 'user+tag@example-domain.com';
        
        $user = (new User())
            ->setName('Special User')
            ->setEmail($email)
            ->setPassword('password');

        $this->repository->save($user);

        $result = $this->repository->findByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals('Special User', $result->getName());
    }

    public function test_save_multiple_users(): void
    {
        $user1 = (new User())
            ->setName('User One')
            ->setEmail('user1@example.com')
            ->setPassword('password1');

        $user2 = (new User())
            ->setName('User Two')
            ->setEmail('user2@example.com')
            ->setPassword('password2');

        $this->repository->save($user1);
        $this->repository->save($user2);

        $this->assertDatabaseHas('users', [
            'email' => 'user1@example.com',
            'name' => 'User One',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user2@example.com',
            'name' => 'User Two',
        ]);
    }

    public function test_save_preserves_all_user_data(): void
    {
        $user = (new User())
            ->setName('Complete User')
            ->setEmail('complete@example.com')
            ->setPassword('secure_hash_123');

        $this->repository->save($user);

        $savedUser = $this->repository->findByEmail('complete@example.com');

        $this->assertEquals('Complete User', $savedUser->getName());
        $this->assertEquals('complete@example.com', $savedUser->getEmail());
        $this->assertEquals('secure_hash_123', $savedUser->getPassword());
    }

    public function test_find_by_email_returns_correct_user_among_many(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->save(
                (new User())
                    ->setName("User $i")
                    ->setEmail("user$i@example.com")
                    ->setPassword("password$i")
            );
        }

        $result = $this->repository->findByEmail('user3@example.com');

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('User 3', $result->getName());
        $this->assertEquals('user3@example.com', $result->getEmail());
    }
}
