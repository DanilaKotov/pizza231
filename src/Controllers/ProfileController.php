<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Product;

// 👇 ДОБАВИТЬ ЭТИ ДВЕ СТРОКИ:
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Views/ProfileTemplate.php';

class ProfileController
{
    /**
     * Страница профиля
     */
    public function index(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit();
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['id']);

        if (!$user) {
            session_destroy();
            header("Location: /login");
            exit();
        }

        // Обновляем сессию
        $_SESSION['user'] = $user;

        // Получаем заказы пользователя
        $orders = $this->getUserOrders($user['email']);

        return \App\Views\ProfileTemplate::getProfileTemplate($user, $orders);
    }

    /**
     * Обновление профиля
     */
    public function update(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit();
        }

        $userId = $_SESSION['user']['id'];
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) {
            $_SESSION['flash'] = "Имя обязательно для заполнения";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        $userModel = new User();
        $result = $userModel->updateProfile($userId, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
        ]);

        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['flash'] = "Профиль успешно обновлен";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash'] = $result['message'];
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: /profile");
        exit();
    }

    /**
     * Загрузка аватара
     */
    public function uploadAvatar(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit();
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = "Ошибка загрузки файла";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['flash'] = "Разрешены только JPG, PNG, GIF, WEBP";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        if ($file['size'] > $maxSize) {
            $_SESSION['flash'] = "Файл слишком большой (макс. 5MB)";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        $uploadDir = __DIR__ . '/../../storage/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'user_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;

        $this->deleteOldAvatar($_SESSION['user']['id']);

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $avatarPath = '/storage/avatars/' . $newFilename;

            $userModel = new User();
            $result = $userModel->updateProfile($_SESSION['user']['id'], ['avatar' => $avatarPath]);

            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                $_SESSION['flash'] = "Аватар успешно загружен";
                $_SESSION['flash_type'] = "success";
            }
        } else {
            $_SESSION['flash'] = "Ошибка при сохранении файла";
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: /profile");
        exit();
    }

    /**
     * Смена пароля
     */
    public function changePassword(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit();
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['id']);

        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash'] = "Неверный текущий пароль";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash'] = "Новые пароли не совпадают";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash'] = "Пароль должен быть не менее 6 символов";
            $_SESSION['flash_type'] = "danger";
            header("Location: /profile");
            exit();
        }

        $result = $userModel->changePassword($user['id'], $newPassword);

        if ($result['success']) {
            $_SESSION['flash'] = "Пароль успешно изменен";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash'] = $result['message'];
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: /profile");
        exit();
    }

    /**
     * Получение заказов пользователя
     */
    private function getUserOrders(string $email): array
    {
        $ordersFile = __DIR__ . '/../../storage/order.json';

        if (!file_exists($ordersFile)) {
            return [];
        }

        $data = file_get_contents($ordersFile);
        $orders = json_decode($data, true);

        if (!is_array($orders)) {
            return [];
        }

        $userOrders = array_filter($orders, fn($order) => ($order['email'] ?? '') === $email);

        usort($userOrders, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return array_values($userOrders);
    }

    /**
     * Удаление старого аватара
     */
    private function deleteOldAvatar(int $userId): void
    {
        $uploadDir = __DIR__ . '/../../storage/avatars/';
        $pattern = $uploadDir . 'user_' . $userId . '_*';
        $files = glob($pattern);

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
