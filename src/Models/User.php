<?php

declare(strict_types=1);

namespace App\Models;

require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';

use App\Configs\Config;
use App\Services\ISaveStorage;

class User
{
    // 👇 НОВОЕ: Для работы с БД через сервис
    private ?ISaveStorage $dataStorage = null;
    private ?string $tableName = null;

    /**
     * 👇 НОВОЕ: Конструктор с внедрением зависимости (для БД)
     */
    public function __construct(?ISaveStorage $service = null, ?string $tableName = null)
    {
        if ($service !== null && $tableName !== null) {
            $this->dataStorage = $service;
            $this->tableName = $tableName;
        }
    }

    // ============================================
    // 📁 СТАРЫЕ МЕТОДЫ (работа с файлом) — БЕЗ ИЗМЕНЕНИЙ
    // ============================================

    /**
     * Регистрация пользователя (файловое хранилище)
     */
    public function register(string $email, string $password, string $name): array
    {
        $users = $this->loadUsers();

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
            }
        }

        $newUser = [
            'id' => count($users) + 1,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'phone' => '',
            'address' => '',
            'avatar' => '/assets/images/default-avatar.png',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $users[] = $newUser;
        $this->saveUsers($users);

        return ['success' => true, 'user' => $newUser];
    }

    /**
     * Вход пользователя (файловое хранилище)
     */
    public function login(string $email, string $password): array
    {
        $users = $this->loadUsers();

        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                unset($user['password']);
                return ['success' => true, 'user' => $user];
            }
        }

        return ['success' => false, 'message' => 'Неверный email или пароль'];
    }

    /**
     * Обновление профиля (файловое хранилище)
     */
    public function updateProfile(int $userId, array $data): array
    {
        $users = $this->loadUsers();

        foreach ($users as $index => $user) {
            if ($user['id'] === $userId) {
                if (isset($data['name'])) $users[$index]['name'] = $data['name'];
                if (isset($data['phone'])) $users[$index]['phone'] = $data['phone'];
                if (isset($data['address'])) $users[$index]['address'] = $data['address'];
                if (isset($data['avatar'])) $users[$index]['avatar'] = $data['avatar'];

                $this->saveUsers($users);
                unset($users[$index]['password']);
                return ['success' => true, 'user' => $users[$index]];
            }
        }

        return ['success' => false, 'message' => 'Пользователь не найден'];
    }

    /**
     * Смена пароля (файловое хранилище)
     */
    public function changePassword(int $userId, string $newPassword): array
    {
        $users = $this->loadUsers();

        foreach ($users as $index => $user) {
            if ($user['id'] === $userId) {
                $users[$index]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->saveUsers($users);
                return ['success' => true];
            }
        }

        return ['success' => false, 'message' => 'Пользователь не найден'];
    }

    /**
     * Поиск пользователя по ID (файловое хранилище)
     */
    public function findById(int $id): ?array
    {
        $users = $this->loadUsers();

        foreach ($users as $user) {
            if ($user['id'] === $id) {
                unset($user['password']);
                return $user;
            }
        }

        return null;
    }

    /**
     * Загрузка всех пользователей из файла
     */
    private function loadUsers(): array
    {
        $filePath = Config::FILE_USERS;
        if (!file_exists($filePath)) return [];
        
        $data = file_get_contents($filePath);
        $users = json_decode($data, true);
        return is_array($users) ? $users : [];
    }

    /**
     * Сохранение пользователей в файл
     */
    private function saveUsers(array $users): void
    {
        $filePath = Config::FILE_USERS;
        $dir = dirname($filePath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $json = json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
    }

    // ============================================
    // 🗄️ НОВЫЕ МЕТОДЫ (работа с БД) — ДОБАВЛЕНО
    // ============================================

    /**
     * 👇 НОВОЕ: Регистрация пользователя в БД
     */
    public function registerToDb(array $userData): bool
    {
        if ($this->dataStorage === null) {
            return false;
        }
        return $this->dataStorage->saveData($this->tableName, $userData);
    }

    /**
     * 👇 НОВОЕ: Поиск пользователя по email в БД
     */
    public function findByEmail(string $email): ?array
    {
        if ($this->dataStorage === null || !method_exists($this->dataStorage, 'findByEmail')) {
            return null;
        }
        return $this->dataStorage->findByEmail($email);
    }

    /**
     * 👇 НОВОЕ: Поиск пользователя по токену в БД
     */
    public function findByToken(string $token): ?array
    {
        if ($this->dataStorage === null || !method_exists($this->dataStorage, 'findByToken')) {
            return null;
        }
        return $this->dataStorage->findByToken($token);
    }

    /**
     * 👇 НОВОЕ: Активация пользователя по ID в БД
     */
    public function verifyEmail(int $userId): bool
    {
        if ($this->dataStorage === null || !method_exists($this->dataStorage, 'verifyUser')) {
            return false;
        }
        return $this->dataStorage->verifyUser($userId);
    }

    /**
     * 👇 НОВОЕ: Проверка пароля (универсальная)
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 👇 НОВОЕ: Проверка уникальности email в БД
     */
    public function isEmailUnique(string $email): bool
    {
        if ($this->dataStorage === null || !method_exists($this->dataStorage, 'uniqueEmail')) {
            return true; // Если нет сервиса — считаем уникальным
        }
        return $this->dataStorage->uniqueEmail($email);
    }
}