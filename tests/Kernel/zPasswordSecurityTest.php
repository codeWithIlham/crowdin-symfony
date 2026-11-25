<?php

namespace App\Tests\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class zPasswordSecurityTest extends KernelTestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testPasswordIsHashed(): void
    {
        $user = new User();
        $plainPassword = 'MySecurePassword123!';

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertNotEmpty($hashedPassword);
    }

    public function testDifferentHashesForSamePassword(): void
    {
        $user1 = new User();
        $user2 = new User();
        $plainPassword = 'SamePassword123!';

        $hash1 = $this->passwordHasher->hashPassword($user1, $plainPassword);
        $hash2 = $this->passwordHasher->hashPassword($user2, $plainPassword);

        $this->assertNotEquals($hash1, $hash2);
    }

    public function testPasswordVerification(): void
    {
        $user = new User();
        $plainPassword = 'VerifyPassword123!';

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->assertTrue($this->passwordHasher->isPasswordValid($user, $plainPassword));
    }

    public function testWrongPasswordFails(): void
    {
        $user = new User();
        $correctPassword = 'CorrectPassword123!';
        $wrongPassword = 'WrongPassword123!';

        $hashedPassword = $this->passwordHasher->hashPassword($user, $correctPassword);
        $user->setPassword($hashedPassword);

        $this->assertFalse($this->passwordHasher->isPasswordValid($user, $wrongPassword));
    }

    public function testPasswordHashUsesArgon2(): void
    {
        $user = new User();
        $plainPassword = 'TestPassword123!';

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        $this->assertStringStartsWith('$argon2', $hashedPassword);
    }

    public function testEmptyPasswordNotAllowed(): void
    {
        $user = new User();
        $emptyPassword = '';

        $hashedPassword = $this->passwordHasher->hashPassword($user, $emptyPassword);

        $this->assertNotEmpty($hashedPassword);
        $this->assertFalse($this->passwordHasher->isPasswordValid($user, $emptyPassword));
    }
}
