<?php
namespace App\Services;

use PDO;
use App\Configs\Config;

class UserDBStorage extends DBStorage implements ISaveStorage
{
    public function loadData(string $name): ?array
    {
        return [];
    }

    public function saveData(string $name, array $data): bool
    {
        $sql = "INSERT INTO users (username, email, password, token, is_verified) 
                VALUES (:username, :email, :password, :token, :is_verified)";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'token' => $data['token'],
            'is_verified' => (int)($data['is_verified'] ?? 0)  // 👈 Приводим к int
        ]);
    }

    public function uniqueEmail(string $email): bool
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() === 0;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->connection->prepare("SELECT id, email, is_verified FROM users WHERE token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            // 👇 ИСПРАВЛЕНО: приводим is_verified к int
            $result['is_verified'] = (int)$result['is_verified'];
        }
        
        return $result ?: null;
    }

    public function verifyUser(int $userId): bool
    {
        $stmt = $this->connection->prepare("UPDATE users SET is_verified = 1, token = '' WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare("SELECT id, username, email, password, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            // 👇 ИСПРАВЛЕНО: приводим is_verified к int
            $result['is_verified'] = (int)$result['is_verified'];
        }
        
        return $result ?: null;
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT id, username, email, is_verified, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            // 👇 ИСПРАВЛЕНО: приводим is_verified к int
            $result['is_verified'] = (int)$result['is_verified'];
        }
        
        return $result ?: null;
    }
}