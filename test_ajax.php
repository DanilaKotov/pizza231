<?php
/**
 * Тест AJAX обновления профиля
 */

require_once __DIR__ . '/src/Configs/Config.php';

// Имитация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Данила',
    'username' => 'Данила',
    'email' => '2008danil04@gmail.com',
];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Не POST запрос']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

echo "POST данные: " . print_r($_POST, true) . "\n";

try {
    $pdo = new PDO(
        Config::MYSQL_DSN,
        Config::MYSQL_USER,
        Config::MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET username = :name, phone = :phone, address = :address 
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        'id' => $_SESSION['user']['id'],
        'name' => $name,
        'phone' => $phone,
        'address' => $address
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Профиль обновлён!']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
