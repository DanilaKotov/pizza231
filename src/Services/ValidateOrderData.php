<?php
namespace App\Services;

class ValidateOrderData
{
    public static function validate(array $data): array
    {
        $errors = [];

        // Валидация ФИО
        $fio = trim($data['fio'] ?? '');
        if (empty($fio)) {
            $errors['fio'] = 'ФИО обязательно';
        } elseif (mb_strlen($fio) < 3) {
            $errors['fio'] = 'ФИО должно быть не менее 3 символов';
        }

        // Валидация адреса
        $address = trim($data['address'] ?? '');
        if (empty($address)) {
            $errors['address'] = 'Адрес обязателен';
        } elseif (mb_strlen($address) < 10) {
            $errors['address'] = 'Адрес должен быть не менее 10 символов';
        } elseif (mb_strlen($address) > 200) {
            $errors['address'] = 'Адрес не должен превышать 200 символов';
        }

        // Валидация телефона
        $phone = trim($data['phone'] ?? '');
        if (empty($phone)) {
            $errors['phone'] = 'Телефон обязателен';
        } else {
            // Оставляем только + и цифры
            $cleanPhone = filter_var($phone, FILTER_CALLBACK, ['options' => function ($value) {
                return preg_replace('/[^+\d]/', '', $value);
            }]);
            if (strlen($cleanPhone) < 10) {
                $errors['phone'] = 'Некорректный номер телефона';
            }
        }

        // Валидация email
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $errors['email'] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        }

        return $errors;
    }

    /**
     * Санитизация данных заказа
     */
    public static function sanitize(array $data): array
    {
        // Оставляем только + и цифры в телефоне
        $cleanPhone = filter_var($data['phone'] ?? '', FILTER_CALLBACK, ['options' => function ($value) {
            return preg_replace('/[^+\d]/', '', $value);
        }]);

        return [
            'fio' => htmlspecialchars(trim($data['fio'] ?? '')),
            'address' => htmlspecialchars(trim($data['address'] ?? '')),
            'phone' => $cleanPhone,
            'email' => htmlspecialchars(trim($data['email'] ?? '')),
        ];
    }
}
