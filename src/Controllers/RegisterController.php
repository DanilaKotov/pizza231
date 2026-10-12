<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Views/RegisterTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/UserDBStorage.php';
require_once __DIR__ . '/../Services/ValidateRegisterData.php';
require_once __DIR__ . '/../Services/Mailer.php';

use App\Models\User;
use App\Views\RegisterTemplate;
use App\Configs\Config;
use App\Services\UserDBStorage;
use App\Services\ValidateRegisterData;
use App\Services\Mailer;

class RegisterController
{
    public function get(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST') {
            return $this->create();
        }
        return RegisterTemplate::getRegisterTemplate();
    }

    private function create(): string
    {
        // Валидация данных
        $errors = ValidateRegisterData::validate($_POST);
        if (!empty($errors)) {
            return RegisterTemplate::getRegisterTemplate($errors, $_POST);
        }

        // Проверка уникальности email
        $userStorage = new UserDBStorage();
        if (!$userStorage->uniqueEmail($_POST['email'])) {
            $errors['email'] = 'Указанный email уже зарегистрирован';
            return RegisterTemplate::getRegisterTemplate($errors, $_POST);
        }

        // Хэширование пароля и генерация токена
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));

        // Подготовка данных для сохранения
        $userData = ValidateRegisterData::sanitize($_POST);
        $userData['password'] = $hashedPassword;
        $userData['token'] = $verificationToken;
        $userData['is_verified'] = 0;

        // Сохранение в БД
        $userModel = new User(new UserDBStorage(), Config::TABLE_USERS);
        $result = $userModel->registerToDb($userData);

        if ($result) {
            // Отправка письма с подтверждением
            Mailer::sendMailUserConfirmation(
                $userData['email'],
                $userData['username'],
                $verificationToken
            );
            return RegisterTemplate::getSuccessTemplate('Письмо с подтверждением отправлено на ваш email!');
        }

        return RegisterTemplate::getRegisterTemplate(['error' => 'Ошибка регистрации. Попробуйте позже.'], $_POST);
    }

        public function verify(): string
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            return RegisterTemplate::getErrorTemplate('Токен подтверждения не предоставлен.');
        }

        $userStorage = new UserDBStorage();
        $user = $userStorage->findByToken($token);

        if (!$user) {
            return RegisterTemplate::getErrorTemplate('Неверный токен подтверждения или email уже подтвержден.');
        }

        // Активация пользователя
        $userStorage->verifyUser($user['id']);

        // 👇 АВТОМАТИЧЕСКИЙ ВХОД В СИСТЕМУ
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Получаем полные данные пользователя
        $userData = $userStorage->getUserById($user['id']);

        // Сохраняем в сессию
        $_SESSION['user'] = [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'email' => $userData['email'],
            'is_verified' => 1
        ];

        // 👇 РЕДИРЕКТ В ПРОФИЛЬ
        header('Location: /profile');
        exit();
    }
}