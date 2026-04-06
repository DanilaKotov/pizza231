<?php
namespace App\Controllers;

use App\Models\Product;

// 👇 НОВОЕ: Подключения конфига и сервисов
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/IStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DatabaseStorage.php';

use App\Configs\Config;

class BasketController
{
    /**
     * Добавление товара в корзину
     */
    public function add(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_POST['id'])) {
            $product_id = (int)$_POST['id'];
            
            // 👇 ИЗМЕНЕНО: Загружаем товар с внедрением зависимости
            if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
                $serviceStorage = new \App\Services\FileStorage();
                $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
            } else {
                $serviceStorage = new \App\Services\DatabaseStorage();
                $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
            }
            
            $allProducts = $productModel->loadData();
            $product = null;
            
            // Ищем товар по ID
            if ($allProducts) {
                foreach ($allProducts as $item) {
                    if ((int)$item['id'] === $product_id) {
                        $product = $item;
                        break;
                    }
                }
            }
            
            // Инициализируем корзину, если нет
            if (!isset($_SESSION['basket'])) {
                $_SESSION['basket'] = [];
            }
            
            // Если товар уже есть — увеличиваем количество
            if (isset($_SESSION['basket'][$product_id])) {
                $_SESSION['basket'][$product_id]['quantity']++;
            } else {
                $_SESSION['basket'][$product_id] = [
                    'quantity' => 1
                ];
            }
            
            // 👇 Флеш-сообщение С НАЗВАНИЕМ ТОВАРА
            $productName = $product ? htmlspecialchars($product['name']) : 'Товар';
            $_SESSION['flash'] = "«{$productName}» успешно добавлен в корзину!";
            $_SESSION['flash_type'] = "success";
        }
        
        // 👇 Редирект назад
        $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$prevUrl}");
        exit();
    }
    
    /**
     * Очистка корзины
     */
    public function clear(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['basket'] = [];
        
        // 👇 Флеш-сообщение
        $_SESSION['flash'] = "Корзина очищена!";
        $_SESSION['flash_type'] = "warning";
        
        // 👇 Редирект
        $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$prevUrl}");
        exit();
    }
}