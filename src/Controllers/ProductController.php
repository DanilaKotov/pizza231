<?php

declare(strict_types=1);

namespace App\Controllers;

// 👇 Подключения моделей, шаблонов и сервисов
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Views/ProductTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/IStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DatabaseStorage.php';

use App\Models\Product;
use App\Views\ProductTemplate;
use App\Configs\Config;

class ProductController
{
    /**
     * Отображение каталога или карточки товара
     */
    public function get(?int $id = null): string
    {
        // 👇 НОВОЕ: Создание модели Product с внедрением зависимости
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            $serviceStorage = new \App\Services\DatabaseStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }

        if ($id === null) {
            // Каталог всех товаров
            $products = $productModel->loadData() ?? [];
            return ProductTemplate::getAllTemplate($products);
        } else {
            // Карточка одного товара
            $products = $productModel->loadData() ?? [];
            $product = $products[$id] ?? null;
            return ProductTemplate::getCardTemplate($product);
        }
    }
}
