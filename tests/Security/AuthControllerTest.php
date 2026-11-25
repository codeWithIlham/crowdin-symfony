<?php

namespace App\Tests\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Create Account', $client->getResponse()->getContent());
    }

    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'register_form[username]' => 'testuser',
            'register_form[email]' => 'test@example.com',
            'register_form[firstName]' => 'John',
            'register_form[lastName]' => 'Doe',
            'register_form[password]' => 'Password123',
        ]);

        $this->assertResponseRedirects('/login');

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@example.com']);

        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('John', $user->getFirstName());
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        $client = static::createClient();

        // First registration
        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'register_form[username]' => 'user1',
            'register_form[email]' => 'duplicate@example.com',
            'register_form[firstName]' => 'John',
            'register_form[lastName]' => 'Doe',
            'register_form[password]' => 'Password123',
        ]);

        // Try to register with same email
        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'register_form[username]' => 'user2',
            'register_form[email]' => 'duplicate@example.com',
            'register_form[firstName]' => 'Jane',
            'register_form[lastName]' => 'Smith',
            'register_form[password]' => 'Password123',
        ]);

        $this->assertResponseIsUnprocessable();
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Sign In', $client->getResponse()->getContent());
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        // Create a user first
        $user = new User();
        $user->setUsername('logintest');
        $user->setEmail('logintest@example.com');
        $user->setFirstName('Login');
        $user->setLastName('Test');

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'TestPassword123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Try to login
        $client->request('GET', '/login');
        $client->submitForm('Sign In', [
            '_username' => 'logintest@example.com',
            '_password' => 'TestPassword123',
        ]);

        $this->assertResponseRedirects('/');
    }

    public function testLoginWithWrongPassword(): void
    {
        $client = static::createClient();

        // Create a user
        $user = new User();
        $user->setUsername('wrongpass');
        $user->setEmail('wrongpass@example.com');
        $user->setFirstName('Wrong');
        $user->setLastName('Pass');

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'CorrectPassword123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Try with wrong password
        $client->request('GET', '/login');
        $client->submitForm('Sign In', [
            '_username' => 'wrongpass@example.com',
            '_password' => 'WrongPassword123',
        ]);

        $this->assertResponseIsUnprocessable();
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        // Create and login user
        $user = new User();
        $user->setUsername('logouttest');
        $user->setEmail('logouttest@example.com');
        $user->setFirstName('Logout');
        $user->setLastName('Test');

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'Password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $client->request('GET', '/login');
        $client->submitForm('Sign In', [
            '_username' => 'logouttest@example.com',
            '_password' => 'Password123',
        ]);

        // Should redirect to home
        $this->assertResponseRedirects('/');

        // Logout
        $client->followRedirect();
        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
