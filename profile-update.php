<?php
require_once __DIR__ . '/src/Configs/Config.php';

use App\Configs\Config;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Неавторизован']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Не POST запрос']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if (Config::STORAGE_TYPE == Config::TYPE_DB) {
    try {
        $pdo = new PDO(
            Config::MYSQL_DSN,
            Config::MYSQL_USER,
            Config::MYSQL_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->prepare("UPDATE users SET username = :name, phone = :phone, address = :address WHERE id = :id");
        
        $result = $stmt->execute([
            'id' => $_SESSION['user']['id'],
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);
        
        $rowCount = $stmt->rowCount();
        
        if ($result && $rowCount > 0) {
            $_SESSION['user']['username'] = $name;
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
            
            echo json_encode(['success' => true, 'message' => 'Профиль успешно обновлен!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Данные не изменились']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Хранилище не настроено']);
}
