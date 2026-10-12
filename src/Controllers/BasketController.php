<?php
namespace App\Controllers;

use App\Models\Product;

require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ILoadStorage.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DBStorage.php';
require_once __DIR__ . '/../Services/ProductDBStorage.php';

use App\Configs\Config;

class BasketController
{
    public function add(): void
    {
        // 👇 ВАЖНО: Запускаем сессию ПЕРЕД любыми действиями
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_POST['id'])) {
            $product_id = (int)$_POST['id'];
            
            // 👇 Загружаем товар для получения названия
            if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
                $serviceStorage = new \App\Services\FileStorage();
                $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
            } else {
                $serviceStorage = new \App\Services\ProductDBStorage();
                $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);
            }
            
            $allProducts = $productModel->loadData();
            $product = null;
            
            if ($allProducts) {
                foreach ($allProducts as $item) {
                    if ((int)$item['id'] === $product_id) {
                        $product = $item;
                        break;
                    }
                }
            }
            
            // 👇 Инициализируем корзину
            if (!isset($_SESSION['basket'])) {
                $_SESSION['basket'] = [];
            }
            
            // 👇 Добавляем или увеличиваем количество
            if (isset($_SESSION['basket'][$product_id])) {
                $_SESSION['basket'][$product_id]['quantity']++;
            } else {
                $_SESSION['basket'][$product_id] = [
                    'id' => $product_id,
                    'quantity' => 1
                ];
            }
            
            // Флеш-сообщение
            $productName = $product ? htmlspecialchars($product['name']) : 'Товар';
            $_SESSION['flash'] = "«{$productName}» добавлен в корзину!";
            $_SESSION['flash_type'] = "success";
        }
        
        $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$prevUrl}");
        exit();
    }
    
    public function clear(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['basket'] = [];
        
        $_SESSION['flash'] = "Корзина очищена!";
        $_SESSION['flash_type'] = "warning";
        
        $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$prevUrl}");
        exit();
    }
}