<?php

namespace Tests\Unit\Entities;

use App\Domain\Entities\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_entity_creation_and_getters(): void
    {
        $user = new User();
        $user->setId(1)
             ->setName('John Doe')
             ->setEmail('john@example.com')
             ->setPassword('hashed_password');

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
    }

    public function test_user_entity_method_chaining(): void
    {
        $user = (new User())
            ->setId(2)
            ->setName('Jane Smith')
            ->setEmail('jane@example.com')
            ->setPassword('another_password');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(2, $user->getId());
        $this->assertEquals('Jane Smith', $user->getName());
    }
}