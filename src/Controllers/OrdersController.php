<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Views\OrderTemplate;
use App\Views\OrdersTemplate;

require_once __DIR__ . '/../Views/BaseTemplate.php';
require_once __DIR__ . '/../Views/OrderTemplate.php';
require_once __DIR__ . '/../Views/OrdersTemplate.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ILoadStorage.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DBStorage.php';
require_once __DIR__ . '/../Services/ProductDBStorage.php';
require_once __DIR__ . '/../Services/OrderDBStorage.php';

use App\Configs\Config;

class OrdersController
{
    /**
     * Список заказов (/orders)
     */
    public function index(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        $user = $_SESSION['user'];
        $userEmail = $user['email'] ?? '';

        $orders = [];
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            try {
                $pdo = new \PDO(
                    Config::MYSQL_DSN,
                    Config::MYSQL_USER,
                    Config::MYSQL_PASSWORD,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                
                // Получаем заказы
                $stmt = $pdo->prepare("
                    SELECT id, fio, email, phone, address, all_sum, created
                    FROM orders
                    WHERE email = ?
                    ORDER BY created DESC
                ");
                $stmt->execute([$userEmail]);
                $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // 👇 Добавляем товары к каждому заказу
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
                    
                    // 👇 Сохраняем как 'products'
                    $order['products'] = $items;
                    
                    error_log("✅ Заказ #" . $order['id'] . " - товаров: " . count($items));
                }
                
                error_log("📦 Всего заказов: " . count($orders));
                
            } catch (\Exception $e) {
                error_log("❌ Get orders error: " . $e->getMessage());
            }
        }

        return OrdersTemplate::getOrdersTemplate($user, $orders);
    }

    /**
     * Просмотр заказа (/orders/view/ID)
     */
        public function view(int $orderId): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        $user = $_SESSION['user'];
        $userEmail = $user['email'] ?? '';
        
        error_log("🔍 Просмотр заказа #$orderId для email: $userEmail");
        
        $order = null;
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            try {
                $pdo = new \PDO(
                    Config::MYSQL_DSN,
                    Config::MYSQL_USER,
                    Config::MYSQL_PASSWORD,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                
                // 👇 ИСПРАВЛЕНО: используем created вместо created_at
                $stmt = $pdo->prepare("
                    SELECT id, fio, email, phone, address, all_sum, created
                    FROM orders
                    WHERE id = ? AND email = ?
                ");
                $stmt->execute([$orderId, $userEmail]);
                $order = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                error_log(" Заказ найден: " . ($order ? 'да' : 'нет'));
                
                if ($order) {
                    // 👇 Загружаем товары
                    $stmtItems = $pdo->prepare("
                        SELECT oi.id, oi.order_id, oi.product_id, oi.count_item, oi.price_item, oi.sum_item,
                            p.name, p.price, p.image
                        FROM order_item oi
                        LEFT JOIN products p ON oi.product_id = p.id_product
                        WHERE oi.order_id = ?
                    ");
                    $stmtItems->execute([$orderId]);
                    $order['products'] = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
                    
                    error_log("🛒 Товаров в заказе: " . count($order['products']));
                }
                
            } catch (\Exception $e) {
                error_log("❌ Get order error: " . $e->getMessage());
            }
        }

        if (!$order) {
            error_log("❌ Заказ #$orderId не найден для email $userEmail");
            return OrdersTemplate::getErrorTemplate('Заказ не найден');
        }

        return OrdersTemplate::getOrderDetailTemplate($user, $order, $orderId);
    }

    /**
     * Оформление заказа (старый функционал - корзина → заказ)
     */
    public function get(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST') {
            return $this->create();
        }
        
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceStorage = new \App\Services\ProductDBStorage();
            $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);
        }
        
        $data = $productModel->getBasketData();
        return OrderTemplate::getOrderTemplate($data);
    }
    
    private function create(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceStorage = new \App\Services\ProductDBStorage();
            $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);
        }
        
        $products = $productModel->getBasketData();
        $orderData = $productModel->prepareData($_POST, $products);
        
        if (isset($_SESSION['user'])) {
            $orderData['user_id'] = $_SESSION['user']['id'];
        }
        
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $orderModel = new Order($serviceStorage, Config::FILE_ORDERS);
        }
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceStorage = new \App\Services\OrderDBStorage();
            $orderModel = new Order($serviceStorage, Config::TABLE_ORDERS);
        }
        
        $orderModel->saveData($orderData);
        $this->sendMail($orderData['email'] ?? '', $orderData);
        $_SESSION['basket'] = [];
        $_SESSION['flash'] = "Спасибо! Ваш заказ успешно создан";
        $_SESSION['flash_type'] = "success";
        
        header("Location: /");
        exit();
    }
    
    public function sendMail($email, $orderData = []) {
        return true;
    }
}