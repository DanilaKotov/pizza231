<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Views/AuthTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/UserDBStorage.php';

use App\Models\User;
use App\Views\AuthTemplate;
use App\Configs\Config;
use App\Services\UserDBStorage;

class AuthController
{
    /**
     * Страница входа
     */
    public function login(): string
    {
        return AuthTemplate::getLoginTemplate();
    }

    /**
     * Страница регистрации (для совместимости)
     */
    public function register(): string
    {
        // Перенаправляем на новый контроллер регистрации
        header('Location: /register');
        exit();
    }

    /**
     * Обработка входа
     */
        public function loginSubmit(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        // 👇 ОТЛАДКА
        error_log("🔍 Попытка входа: email=$email");

        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $userStorage = new UserDBStorage();
            $user = $userStorage->findByEmail($email);

            if (!$user) {
                error_log("❌ Пользователь не найден");
                $errors[] = 'Неверный email или пароль';
                return AuthTemplate::getLoginTemplate($errors);
            }

            error_log("✅ Пользователь найден: ID={$user['id']}");
            error_log("🔐 Хэш из БД: " . substr($user['password'], 0, 20) . "...");

            if (!password_verify($password, $user['password'])) {
                error_log("❌ Пароль не совпадает");
                $errors[] = 'Неверный email или пароль';
                return AuthTemplate::getLoginTemplate($errors);
            }

            error_log("✅ Пароль верный");

            // 👇 ИСПРАВЛЕНО: явное сравнение вместо empty()
            if ((int)$user['is_verified'] !== 1) {
                error_log("❌ Email не подтверждён: is_verified = " . $user['is_verified']);
                $errors[] = 'Подтвердите email перед входом';
                return AuthTemplate::getLoginTemplate($errors);
            }

            // ✅ Сохраняем в сессию
            $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['username'] ?? $user['name'] ?? 'Пользователь',
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'avatar' => $user['avatar'] ?? '',
            'created_at' => $user['created_at'] ?? date('Y-m-d H:i:s'),
            'is_verified' => 1
            ];

            error_log("✅ Сессия сохранена: " . print_r($_SESSION['user'], true));

        } else {
            // Файловое хранилище
            $userModel = new User();
            $result = $userModel->login($email, $password);

            if (!$result['success']) {
                $errors[] = $result['message'] ?? 'Неверный email или пароль';
                return AuthTemplate::getLoginTemplate($errors);
            }

            $_SESSION['user'] = $result['user'];
        }

        $_SESSION['flash'] = 'Добро пожаловать, ' . ($_SESSION['user']['username'] ?? 'Пользователь') . '!';
        $_SESSION['flash_type'] = 'success';

        error_log(" Редирект на /profile");
        
        // 👇 ВАЖНО: закрываем сессию перед редиректом
        session_write_close();
        
        header('Location: /profile');
        exit();
    }

    /**
     * Выход из аккаунта
     */
    public function logout(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        $_SESSION['flash'] = 'Вы вышли из аккаунта';
        $_SESSION['flash_type'] = 'info';

        header('Location: /');
        exit();
    }
}