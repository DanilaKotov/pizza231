<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use App\Services\ValidateRegisterData;

require_once __DIR__ . '/../src/Services/ValidateRegisterData.php';

class ValidateRegisterDataTest extends TestCase
{
    /**
     * Тест-1: Проверка успешной валидации корректных данных
     */
    public function testValidateSuccess(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertEmpty($errors, 'Ошибок не должно быть для корректных данных');
    }

    /**
     * Тест-2: Проверка валидации пустого имени пользователя
     */
    public function testValidateEmptyUsername(): void
    {
        $data = [
            'username' => '',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('username', $errors);
        $this->assertEquals('Имя пользователя обязательно', $errors['username']);
    }

    /**
     * Тест-3: Проверка валидации пустого email
     */
    public function testValidateEmptyEmail(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => '',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('email', $errors);
        $this->assertEquals('Email обязателен', $errors['email']);
    }

    /**
     * Тест-4: Проверка валидации некорректного email
     */
    public function testValidateInvalidEmail(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('email', $errors);
        $this->assertEquals('Некорректный email', $errors['email']);
    }

    /**
     * Тест-5: Проверка валидации пустого пароля
     */
    public function testValidateEmptyPassword(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'john@example.com',
            'password' => '',
            'confirm_password' => '',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('password', $errors);
        $this->assertEquals('Пароль обязателен', $errors['password']);
        $this->assertArrayHasKey('confirm_password', $errors);
    }

    /**
     * Тест-6: Проверка валидации слишком короткого пароля
     */
    public function testValidateShortPassword(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'john@example.com',
            'password' => '12345',
            'confirm_password' => '12345',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('password', $errors);
        $this->assertEquals('Пароль должен быть не менее 6 символов', $errors['password']);
    }

    /**
     * Тест-7: Проверка валидации несовпадающих паролей
     */
    public function testValidatePasswordMismatch(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirm_password' => 'password456',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('confirm_password', $errors);
        $this->assertEquals('Пароли не совпадают', $errors['confirm_password']);
    }

    /**
     * Тест-8: Проверка валидации пустого подтверждения пароля
     */
    public function testValidateEmptyConfirmPassword(): void
    {
        $data = [
            'username' => 'JohnDoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirm_password' => '',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertArrayHasKey('confirm_password', $errors);
        $this->assertEquals('Подтверждение пароля обязательно', $errors['confirm_password']);
    }

    /**
     * Тест-9: Проверка санитизации данных
     */
    public function testSanitizeRemovesXSS(): void
    {
        $data = [
            'username' => '<script>alert("XSS")</script>John',
            'email' => '"><img src=x onerror=alert(1)>@example.com',
            'password' => 'password123',
        ];

        $sanitized = ValidateRegisterData::sanitize($data);

        $this->assertStringNotContainsString('<script>', $sanitized['username']);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized['username']);
        $this->assertStringNotContainsString('<img', $sanitized['email']);
        $this->assertStringContainsString('&lt;img', $sanitized['email']);
    }

    /**
     * Тест-10: Проверка санитизации убирает лишние пробелы
     */
    public function testSanitizeTrimsWhitespace(): void
    {
        $data = [
            'username' => '   JohnDoe   ',
            'email' => '  john@example.com  ',
            'password' => 'password123',
        ];

        $sanitized = ValidateRegisterData::sanitize($data);

        $this->assertEquals('JohnDoe', $sanitized['username']);
        $this->assertEquals('john@example.com', $sanitized['email']);
    }

    /**
     * Тест-11: Проверка санитизации убирает HTML теги
     */
    public function testSanitizeRemovesTags(): void
    {
        $data = [
            'username' => '<b>Bold</b> John',
            'email' => '<script>evil</script>john@example.com',
            'password' => 'password123',
        ];

        $sanitized = ValidateRegisterData::sanitize($data);

        $this->assertStringNotContainsString('<b>', $sanitized['username']);
        $this->assertStringNotContainsString('</b>', $sanitized['username']);
    }

    /**
     * Тест-12: Проверка множественных ошибок валидации
     */
    public function testValidateMultipleErrors(): void
    {
        $data = [
            'username' => '',
            'email' => 'invalid',
            'password' => '123',
            'confirm_password' => '456',
        ];

        $errors = ValidateRegisterData::validate($data);

        $this->assertCount(4, $errors);
        $this->assertArrayHasKey('username', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertArrayHasKey('confirm_password', $errors);
    }
}
