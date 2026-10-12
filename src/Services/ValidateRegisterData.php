<?php
namespace App\Services;

class ValidateRegisterData
{
    public static function validate(array $data): array
    {
        $errors = [];

        // Валидация имени пользователя
        $username = trim($data['username'] ?? '');
        if (empty($username)) {
            $errors['username'] = 'Имя пользователя обязательно';
        }

        // Валидация email
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $errors['email'] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        }

        // Валидация пароля
        $password = $data['password'] ?? '';
        if (empty($password)) {
            $errors['password'] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен быть не менее 6 символов';
        }

        // Валидация подтверждения пароля
        $confirmPassword = $data['confirm_password'] ?? '';
        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'Подтверждение пароля обязательно';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }

        return $errors;
    }

    /**
     * Проверка уникальности email
     */
    public static function checkUniqueEmail(string $email, UserDBStorage $userStorage): ?string
    {
        if (!$userStorage->uniqueEmail($email)) {
            return 'Указанный email уже зарегистрирован';
        }
        return null;
    }

    /**
     * Санитизация данных регистрации
     */
    public static function sanitize(array $data): array
    {
        return [
            'username' => htmlspecialchars(trim($data['username'] ?? '')),
            'email' => htmlspecialchars(trim($data['email'] ?? '')),
            'password' => $data['password'] ?? '',
        ];
    }
}