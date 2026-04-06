<?php
namespace App\Models;

require_once __DIR__ . '/../Configs/Config.php';

use App\Configs\Config;

class User
{
    /**
     * Регистрация пользователя
     */
    public function register(string $email, string $password, string $name): array
    {
        $users = $this->loadUsers();
        
        // Проверка существования email
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
            }
        }
        
        // Создание нового пользователя
        $newUser = [
            'id' => count($users) + 1,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'phone' => '',
            'address' => '',
            'avatar' => '/assets/images/default-avatar.png',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        $this->saveUsers($users);
        
        return ['success' => true, 'user' => $newUser];
    }
    
    /**
     * Вход пользователя
     */
    public function login(string $email, string $password): array
    {
        $users = $this->loadUsers();
        
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                return ['success' => true, 'user' => $user];
            }
        }
        
        return ['success' => false, 'message' => 'Неверный email или пароль'];
    }
    
    /**
     * Обновление профиля
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
                return ['success' => true, 'user' => $users[$index]];
            }
        }
        
        return ['success' => false, 'message' => 'Пользователь не найден'];
    }
    
    /**
     * Смена пароля
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
     * Загрузка всех пользователей
     */
    private function loadUsers(): array
    {
        $filePath = Config::FILE_USERS;
        
        if (!file_exists($filePath)) {
            return [];
        }
        
        $data = file_get_contents($filePath);
        $users = json_decode($data, true);
        
        return is_array($users) ? $users : [];
    }
    
    /**
     * Сохранение пользователей
     */
    private function saveUsers(array $users): void
    {
        $filePath = Config::FILE_USERS;
        $dir = dirname($filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $json = json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
    }
    
    /**
     * Поиск пользователя по ID
     */
    public function findById(int $id): ?array
    {
        $users = $this->loadUsers();
        
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        
        return null;
    }
}