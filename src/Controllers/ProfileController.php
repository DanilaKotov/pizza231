<?php
namespace App\Controllers;

require_once __DIR__ . '/../Views/ProfileTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';

use App\Views\ProfileTemplate;
use App\Configs\Config;

class ProfileController
{
    public function index(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        error_log("🔍 ProfileController::index() - сессия user: " . print_r($_SESSION['user'], true));

        // Получаем актуальные данные из БД
        $user = $this->getUserFromDb($_SESSION['user']['id']);
        
        // Если в БД нет — берём из сессии
        if (!$user) {
            error_log("⚠️ Пользователь не найден в БД, используем сессию");
            $user = $_SESSION['user'];
        }

        error_log("📊 Данные пользователя для шаблона: " . print_r($user, true));
        error_log("🖼️ Avatar from DB/session: " . ($user['avatar'] ?? 'NULL'));

        // Получаем заказы
        $orders = [];
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            error_log("🔍 Запрашиваем заказы для user_id=" . $user['id']);
            $orders = $this->getUserOrders($user['id']);
            error_log("📦 Итого заказов для шаблона: " . count($orders));
        }

        return ProfileTemplate::getProfileTemplate($user, $orders);
    }

    public function update(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        error_log("🔴 PROFILE UPDATE CALLED");
        error_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
        error_log("POST data: " . print_r($_POST, true));
        error_log("SESSION user: " . print_r($_SESSION['user'], true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            error_log("📝 Received data: name='$name', phone='$phone', address='$address'");

            if (Config::STORAGE_TYPE == Config::TYPE_DB) {
                try {
                    $pdo = new \PDO(
                        Config::MYSQL_DSN,
                        Config::MYSQL_USER,
                        Config::MYSQL_PASSWORD,
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    
                    error_log("🔄 Начало обновления профиля: user_id={$_SESSION['user']['id']}, name=$name, phone=$phone, address=$address");
                    
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
                    
                    $rowCount = $stmt->rowCount();
                    error_log("✅ UPDATE выполнен, rowCount=$rowCount");
                    
                    if ($result && $rowCount > 0) {
                        // Обновляем сессию - используем username для совместимости
                        $_SESSION['user']['username'] = $name;
                        $_SESSION['user']['name'] = $name;
                        $_SESSION['user']['phone'] = $phone;
                        $_SESSION['user']['address'] = $address;
                        
                        $_SESSION['flash'] = '✅ Профиль успешно обновлён!';
                        $_SESSION['flash_type'] = 'success';
                        
                        error_log("✅ Профиль обновлён: name=$name, phone=$phone, address=$address");
                    } else {
                        error_log("⚠️ UPDATE не обновил ни одной строки");
                        $_SESSION['flash'] = '⚠️ Данные не изменились или ошибка';
                        $_SESSION['flash_type'] = 'warning';
                    }
                    
                } catch (\Exception $e) {
                    $_SESSION['flash'] = '❌ Ошибка: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                    error_log("❌ Profile update error: " . $e->getMessage());
                }
            } else {
                error_log("❌ STORAGE_TYPE не TYPE_DB: " . Config::STORAGE_TYPE);
                $_SESSION['flash'] = '❌ Ошибка: хранилище не настроено';
                $_SESSION['flash_type'] = 'danger';
            }
        } else {
            error_log("⚠️ Не POST запрос");
            $_SESSION['flash'] = '⚠️ Неверный метод запроса';
            $_SESSION['flash_type'] = 'warning';
        }

        header('Location: /profile');
        exit();
    }

    public function updateAjax(): string
    {
        /**
         * Метод для AJAX запросов
         * Возвращает JSON строку
         */
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            return json_encode(['success' => false, 'message' => 'Неавторизован']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        error_log("🔴 AJAX UPDATE: name=$name, phone=$phone, address=$address");

        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            try {
                $pdo = new \PDO(
                    Config::MYSQL_DSN,
                    Config::MYSQL_USER,
                    Config::MYSQL_PASSWORD,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                
                error_log("🔄 Начало AJAX обновления профиля: user_id={$_SESSION['user']['id']}");
                
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
                
                $rowCount = $stmt->rowCount();
                error_log("✅ AJAX UPDATE выполнен, rowCount=$rowCount");
                
                if ($result && $rowCount > 0) {
                    $_SESSION['user']['username'] = $name;
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['phone'] = $phone;
                    $_SESSION['user']['address'] = $address;
                    
                    error_log("✅ AJAX Профиль обновлён");
                    
                    return json_encode(['success' => true, 'message' => 'Профиль успешно обновлён!']);
                } else {
                    error_log("⚠️ AJAX UPDATE не обновил строки");
                    return json_encode(['success' => false, 'message' => 'Данные не изменились']);
                }
                
            } catch (\Exception $e) {
                error_log("❌ AJAX Profile update error: " . $e->getMessage());
                return json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
            }
        }
        
        return json_encode(['success' => false, 'message' => 'Хранилище не настроено']);
    }

    public function uploadAvatar(): string
    {
        error_log("🚀 uploadAvatar() CALLED!");
        error_log("📥 FILES: " . print_r($_FILES, true));
        error_log("📥 SESSION: " . print_r($_SESSION['user'] ?? 'no user', true));
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || !isset($_FILES['avatar'])) {
            error_log("❌ uploadAvatar: No user or no file");
            $_SESSION['flash'] = '❌ Ошибка загрузки файла';
            $_SESSION['flash_type'] = 'danger';
            header('Location: /profile');
            exit();
        }

        $file = $_FILES['avatar'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = '❌ Ошибка загрузки: код ' . $file['error'];
            $_SESSION['flash_type'] = 'danger';
            header('Location: /profile');
            exit();
        }
        
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            $_SESSION['flash'] = '❌ Недопустимый формат (разрешены JPG, PNG, GIF, WEBP)';
            $_SESSION['flash_type'] = 'danger';
            header('Location: /profile');
            exit();
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'avatar_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../Storage/avatars/' . $newName;

        error_log("📁 Upload path: " . $uploadPath);
        error_log("📁 Upload dir exists: " . (is_dir(dirname($uploadPath)) ? 'yes' : 'no'));

        if (!is_dir(dirname($uploadPath))) {
            mkdir(dirname($uploadPath), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("✅ File moved successfully to: " . $uploadPath);
            error_log("🔍 File exists after move: " . (file_exists($uploadPath) ? 'yes' : 'no'));
            
            if (Config::STORAGE_TYPE == Config::TYPE_DB) {
                try {
                    $pdo = new \PDO(
                        Config::MYSQL_DSN,
                        Config::MYSQL_USER,
                        Config::MYSQL_PASSWORD,
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    
                    $avatarPath = '/Storage/avatars/' . $newName;
                    error_log("💾 Saving to DB avatar path: " . $avatarPath);
                    
                    $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
                    $stmt->execute([
                        'id' => $_SESSION['user']['id'],
                        'avatar' => $avatarPath
                    ]);
                    
                    error_log("✅ DB updated, rows affected: " . $stmt->rowCount());
                    
                    // Обновляем сессию
                    $_SESSION['user']['avatar'] = $avatarPath;
                    error_log("📋 Session avatar updated to: " . $_SESSION['user']['avatar']);
                    
                    $_SESSION['flash'] = '✅ Аватар успешно обновлён!';
                    $_SESSION['flash_type'] = 'success';
                    
                } catch (\Exception $e) {
                    $_SESSION['flash'] = '❌ Ошибка БД: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                    error_log("❌ Avatar update error: " . $e->getMessage());
                }
            }
        } else {
            $_SESSION['flash'] = '❌ Не удалось сохранить файл';
            $_SESSION['flash_type'] = 'danger';
            error_log("❌ Failed to move uploaded file");
        }

        header('Location: /profile?t=' . time());
        exit();
    }

    public function changePassword(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($new) < 6) {
                $_SESSION['flash'] = 'Пароль должен быть не менее 6 символов';
                $_SESSION['flash_type'] = 'danger';
                header('Location: /profile');
                exit();
            }

            if ($new !== $confirm) {
                $_SESSION['flash'] = 'Пароли не совпадают';
                $_SESSION['flash_type'] = 'danger';
                header('Location: /profile');
                exit();
            }

            if (Config::STORAGE_TYPE == Config::TYPE_DB) {
                try {
                    $pdo = new \PDO(
                        Config::MYSQL_DSN,
                        Config::MYSQL_USER,
                        Config::MYSQL_PASSWORD,
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user']['id']]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($current, $user['password'])) {
                        $hashed = password_hash($new, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed, $_SESSION['user']['id']]);
                        
                        $_SESSION['flash'] = 'Пароль успешно изменён!';
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash'] = 'Неверный текущий пароль';
                        $_SESSION['flash_type'] = 'danger';
                    }
                    
                } catch (\Exception $e) {
                    $_SESSION['flash'] = 'Ошибка: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }

        header('Location: /profile');
        exit();
    }

    private function getUserFromDb(int $userId): ?array
    {
        try {
            $pdo = new \PDO(
                Config::MYSQL_DSN,
                Config::MYSQL_USER,
                Config::MYSQL_PASSWORD,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Маппинг username -> name для совместимости с шаблоном
            if ($user && isset($user['username']) && !isset($user['name'])) {
                $user['name'] = $user['username'];
            }
            
            // Логирование avatar поля
            if ($user) {
                error_log("🔍 getUserFromDb - avatar field: " . ($user['avatar'] ?? 'NULL'));
            }
            
            return $user;
            
        } catch (\Exception $e) {
            error_log("❌ Get user error: " . $e->getMessage());
            return null;
        }
    }

    private function getUserOrders(int $userId): array
    {
        try {
            $pdo = new \PDO(
                Config::MYSQL_DSN,
                Config::MYSQL_USER,
                Config::MYSQL_PASSWORD,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Получаем email пользователя
            $stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("❌ Пользователь не найден: id=$userId");
                return [];
            }
            
            $userEmail = $user['email'];
            error_log("🔍 Поиск заказов для email: $userEmail");
            
            // Получаем заказы
            $stmt = $pdo->prepare("
                SELECT o.id, o.fio, o.email, o.phone, o.address, o.all_sum, o.created
                FROM orders o
                WHERE o.email = ?
                ORDER BY o.created DESC
            ");
            $stmt->execute([$userEmail]);
            
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("📦 Найдено заказов: " . count($orders));
            
            // Добавляем товары к каждому заказу
            foreach ($orders as &$order) {
                $stmtItems = $pdo->prepare("
                    SELECT oi.id, oi.order_id, oi.product_id, oi.count_item, oi.price_item, oi.sum_item,
                           p.name, p.price, p.image
                    FROM order_item oi
                    LEFT JOIN products p ON oi.product_id = p.id_product
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
                
                $order['products'] = $items;
                
                error_log("✅ Заказ #" . $order['id'] . " - товаров: " . count($items));
                
                // Отладка: выводим названия товаров
                if (!empty($items)) {
                    foreach ($items as $item) {
                        error_log("   - " . ($item['name'] ?? 'NULL'));
                    }
                }
            }
            
            return $orders;
            
        } catch (\Exception $e) {
            error_log("❌ Get orders error: " . $e->getMessage());
            return [];
        }
    }
}