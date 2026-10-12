<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Views/ProductTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';

// 👇 НОВОЕ: Подключения интерфейсов
require_once __DIR__ . '/../Services/ILoadStorage.php';      // 👈 Добавить
require_once __DIR__ . '/../Services/ISaveStorage.php';      // 👈 Добавить
require_once __DIR__ . '/../Services/IStorage.php';          // 👈 Добавить
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DBStorage.php';
require_once __DIR__ . '/../Services/ProductDBStorage.php';

use App\Models\Product;
use App\Views\ProductTemplate;
use App\Configs\Config;
use App\Services\ProductDBStorage;

class ProductController
{
    public function get(?int $id = null): string
    {
        // 👇 ИЗМЕНЕНО: создание модели с поддержкой БД
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceStorage = new ProductDBStorage();  // 👇 НОВОЕ
            $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);  // 👇 НОВОЕ
        }
        
        if ($id === null) {
            $products = $productModel->loadData() ?? [];
            return ProductTemplate::getAllTemplate($products);
        } else {
            $products = $productModel->loadData() ?? [];
            $product = $products[$id] ?? null;
            return ProductTemplate::getCardTemplate($product);
        }
    }
}