<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setPassword('hashed_password');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
        $this->assertTrue(in_array('ROLE_TEACHER', $user->getRoles()));
    }

    public function testUserRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_TEACHER', 'ROLE_ADMIN']);

        $this->assertContains('ROLE_TEACHER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
