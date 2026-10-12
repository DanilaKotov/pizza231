<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use App\Services\ValidateOrderData;

require_once __DIR__ . '/../src/Services/ValidateOrderData.php';

class ValidateOrderDataTest extends TestCase
{
    /**
     * Тест-1: Проверка успешной валидации корректных данных
     */
    public function testValidateSuccess(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10, кв. 5',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertEmpty($errors, 'Ошибок не должно быть для корректных данных');
    }

    /**
     * Тест-2: Проверка валидации пустого ФИО
     */
    public function testValidateEmptyFIO(): void
    {
        $data = [
            'fio' => '',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('fio', $errors);
        $this->assertEquals('ФИО обязательно', $errors['fio']);
    }

    /**
     * Тест-3: Проверка валидации слишком короткого ФИО
     */
    public function testValidateShortFIO(): void
    {
        $data = [
            'fio' => 'И',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('fio', $errors);
        $this->assertEquals('ФИО должно быть не менее 3 символов', $errors['fio']);
    }

    /**
     * Тест-4: Проверка валидации пустого адреса
     */
    public function testValidateEmptyAddress(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => '',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('address', $errors);
        $this->assertEquals('Адрес обязателен', $errors['address']);
    }

    /**
     * Тест-5: Проверка валидации слишком короткого адреса
     */
    public function testValidateShortAddress(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'ул. Л 1',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('address', $errors);
        $this->assertEquals('Адрес должен быть не менее 10 символов', $errors['address']);
    }

    /**
     * Тест-6: Проверка валидации слишком длинного адреса
     */
    public function testValidateLongAddress(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => str_repeat('a', 201),
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('address', $errors);
        $this->assertEquals('Адрес не должен превышать 200 символов', $errors['address']);
    }

    /**
     * Тест-7: Проверка валидации пустого телефона
     */
    public function testValidateEmptyPhone(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('phone', $errors);
        $this->assertEquals('Телефон обязателен', $errors['phone']);
    }

    /**
     * Тест-8: Проверка валидации некорректного телефона (слишком короткий)
     */
    public function testValidateInvalidPhone(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '12345',
            'email' => 'ivanov@example.com',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('phone', $errors);
        $this->assertEquals('Некорректный номер телефона', $errors['phone']);
    }

    /**
     * Тест-9: Проверка валидации пустого email
     */
    public function testValidateEmptyEmail(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => '',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('email', $errors);
        $this->assertEquals('Email обязателен', $errors['email']);
    }

    /**
     * Тест-10: Проверка валидации некорректного email
     */
    public function testValidateInvalidEmail(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'invalid-email',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertArrayHasKey('email', $errors);
        $this->assertEquals('Некорректный email', $errors['email']);
    }

    /**
     * Тест-11: Проверка санитизации данных - удаление спецсимволов из телефона
     */
    public function testSanitizePhoneRemovesNonNumeric(): void
    {
        $data = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Москва, ул. Ленина, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.com',
        ];

        $sanitized = ValidateOrderData::sanitize($data);

        $this->assertEquals('+79991234567', $sanitized['phone']);
    }

    /**
     * Тест-12: Проверка санитизации данных - экранирование XSS
     */
    public function testSanitizeRemovesXSS(): void
    {
        $data = [
            'fio' => '<script>alert("XSS")</script>Иванов',
            'address' => '"><img src=x onerror=alert(1)>',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'test@example.com',
        ];

        $sanitized = ValidateOrderData::sanitize($data);

        $this->assertStringNotContainsString('<script>', $sanitized['fio']);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized['fio']);
        $this->assertStringNotContainsString('<img', $sanitized['address']);
        $this->assertStringContainsString('&lt;img', $sanitized['address']);
    }

    /**
     * Тест-12: Проверка множественных ошибок валидации
     */
    public function testValidateMultipleErrors(): void
    {
        $data = [
            'fio' => 'И',
            'address' => 'а',
            'phone' => '123',
            'email' => 'invalid',
        ];

        $errors = ValidateOrderData::validate($data);

        $this->assertCount(4, $errors);
        $this->assertArrayHasKey('fio', $errors);
        $this->assertArrayHasKey('address', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Тест-14: Проверка санитизации убирает лишние пробелы
     */
    public function testSanitizeTrimsWhitespace(): void
    {
        $data = [
            'fio' => '   Иванов   ',
            'address' => '  г. Москва, ул. Ленина, д. 10  ',
            'phone' => '  +7 (999) 123-45-67  ',
            'email' => '  ivanov@example.com  ',
        ];

        $sanitized = ValidateOrderData::sanitize($data);

        $this->assertEquals('Иванов', $sanitized['fio']);
        $this->assertEquals('г. Москва, ул. Ленина, д. 10', $sanitized['address']);
        $this->assertEquals('+79991234567', $sanitized['phone']);
        $this->assertEquals('ivanov@example.com', $sanitized['email']);
    }
}
