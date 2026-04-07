<?php

declare(strict_types=1);

namespace App\Controllers;

// 👇 ДОБАВИТЬ ЭТИ ДВЕ СТРОКИ:
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Views/AuthTemplate.php';

use App\Models\User;

class AuthController
{
    /**
     * Страница регистрации
     */
    public function register(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            header("Location: /profile");
            exit();
        }

        return \App\Views\AuthTemplate::getRegisterTemplate();
    }

    /**
     * Обработка регистрации
     */
    public function registerSubmit(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /register");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $name = trim($_POST['name'] ?? '');

        if (empty($email) || empty($password) || empty($name)) {
            $_SESSION['flash'] = "Все поля обязательны для заполнения";
            $_SESSION['flash_type'] = "danger";
            header("Location: /register");
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['flash'] = "Пароли не совпадают";
            $_SESSION['flash_type'] = "danger";
            header("Location: /register");
            exit();
        }

        if (strlen($password) < 6) {
            $_SESSION['flash'] = "Пароль должен быть не менее 6 символов";
            $_SESSION['flash_type'] = "danger";
            header("Location: /register");
            exit();
        }

        $userModel = new User();  // ← Теперь работает!
        $result = $userModel->register($email, $password, $name);

        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['flash'] = "Регистрация успешна!";
            $_SESSION['flash_type'] = "success";
            header("Location: /profile");
            exit();
        } else {
            $_SESSION['flash'] = $result['message'];
            $_SESSION['flash_type'] = "danger";
            header("Location: /register");
            exit();
        }
    }

    /**
     * Страница входа
     */
    public function login(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            header("Location: /profile");
            exit();
        }

        return \App\Views\AuthTemplate::getLoginTemplate();
    }

    /**
     * Обработка входа
     */
    public function loginSubmit(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash'] = "Введите email и пароль";
            $_SESSION['flash_type'] = "danger";
            header("Location: /login");
            exit();
        }

        $userModel = new User();  // ← Теперь работает!
        $result = $userModel->login($email, $password);

        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['flash'] = "С возвращением, " . $result['user']['name'] . "!";
            $_SESSION['flash_type'] = "success";
            header("Location: /profile");
            exit();
        } else {
            $_SESSION['flash'] = $result['message'];
            $_SESSION['flash_type'] = "danger";
            header("Location: /login");
            exit();
        }
    }

    /**
     * Выход
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user']);
        $_SESSION['flash'] = "Вы вышли из системы";
        $_SESSION['flash_type'] = "info";

        header("Location: /");
        exit();
    }
}
