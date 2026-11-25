<?php

namespace App\Tests\Validation;

use App\Entity\User;
use App\Form\RegisterFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class RegisterFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'Password123',
        ];

        $objectToCompare = new User();

        $form = $this->factory->create(RegisterFormType::class, $objectToCompare);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($objectToCompare->getUsername(), 'testuser');
        $this->assertEquals($objectToCompare->getEmail(), 'test@example.com');
    }

    public function testUsernameIsRequired(): void
    {
        $formData = [
            'username' => '',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'Password123',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->get('username')->getErrors());
    }

    public function testEmailIsRequired(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => '',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'Password123',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    public function testPasswordMinLength(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'short',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    public function testUsernameMinLength(): void
    {
        $formData = [
            'username' => 'ab',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'Password123',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    public function testFirstNameIsOptional(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => '',
            'lastName' => 'Doe',
            'password' => 'Password123',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isValid());
    }

    public function testLastNameIsOptional(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => '',
            'password' => 'Password123',
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isValid());
    }
}
