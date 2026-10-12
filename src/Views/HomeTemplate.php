<?php
namespace App\Views;

require_once __DIR__ . '/BaseTemplate.php';
require_once __DIR__ . '/../Models/Product.php';

// 👇 Подключения сервисов и конфигов
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ILoadStorage.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DBStorage.php';
require_once __DIR__ . '/../Services/ProductDBStorage.php';

use App\Models\Product;
use App\Configs\Config;

class HomeTemplate extends BaseTemplate
{
    public static function getTemplate(string $content = ''): string 
    {
        // 👇 СОЗДАНИЕ МОДЕЛИ С ВНЕДРЕНИЕМ ЗАВИСИМОСТИ
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            // ✅ Для БД используем ProductDBStorage + TABLE_PRODUCTS
            $serviceStorage = new \App\Services\ProductDBStorage();
            $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);
        }
        
        // Загружаем товары
        $products = $productModel->loadData() ?? [];
        
        // Генерируем HTML для карточек
        $productsHtml = self::renderProducts($products);

        // Основной контент
        $ourContent = '
        <!-- Герой-блок -->
        <div class="hero-section text-center">
            <div class="container">
                <h1 class="display-4 fw-bold">
                    Добро пожаловать в компьютерный магазин<br>
                    <span class="text-accent">"Пиксель"</span>
                </h1>
                <p class="lead">Лучшие комплектующие для вашего ПК</p>
                <a href="/products" class="btn btn-light text-dark btn-lg mt-3">Каталог</a>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-md-6">
                    <h2 class="mb-3">Почему выбирают нас?</h2>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent"><i class="bi bi-check-circle-fill text-success"></i> Оригинальные комплектующие</li>
                        <li class="list-group-item bg-transparent"><i class="bi bi-check-circle-fill text-success"></i> Гарантия качества</li>
                        <li class="list-group-item bg-transparent"><i class="bi bi-check-circle-fill text-success"></i> Доступные цены</li>
                    </ul>
                </div>
            </div>
            
            <!-- Секция с товарами -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-4">🔥 Популярные товары</h2>
                    ' . $productsHtml . '
                </div>
            </div>
        </div>
        ';
        
        return parent::getTemplate($ourContent);
    }
    
    /**
     * Рендерит карточки товаров
     */
        private static function renderProducts(array $products): string
    {
        if (empty($products)) {
            return '<p class="text-center text-muted">Товары временно отсутствуют</p>';
        }

        // Берём только первые 6 товаров для главной
        $products = array_slice($products, 0, 6);
        
        $html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        
        foreach ($products as $product) {
            $name = htmlspecialchars($product['name'] ?? 'Без названия');
            $description = htmlspecialchars($product['description'] ?? '');
            $price = number_format($product['price'] ?? 0, 0, '.', ' ');
            $image = $product['image'] ?? '';
            $id = (int)($product['id'] ?? 0);
            
            // 👇 УНИВЕРСАЛЬНЫЙ ПУТЬ: проверяем несколько вариантов
            if (!empty($image)) {
                // Если уже полный путь
                if (strpos($image, '/') === 0 || strpos($image, 'http') === 0) {
                    $imgSrc = $image;
                } else {
                    // Если только имя файла — пробуем разные папки
                    if (file_exists(__DIR__ . '/../../uploads/' . $image)) {
                        $imgSrc = '/uploads/' . $image;
                    } elseif (file_exists(__DIR__ . '/../../assets/images/' . $image)) {
                        $imgSrc = '/assets/images/' . $image;
                    } else {
                        $imgSrc = 'https://via.placeholder.com/300x200?text=' . urlencode($name);
                    }
                }
            } else {
                $imgSrc = 'https://via.placeholder.com/300x200?text=Нет+фото';
            }
            
            $html .= '
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="' . $imgSrc . '" 
                        class="card-img-top" 
                        alt="' . $name . '"
                        style="height: 200px; object-fit: cover;"
                        onerror="this.src=\'https://via.placeholder.com/300x200?text=Ошибка+фото\';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">' . $name . '</h5>
                        <p class="card-text text-muted small flex-grow-1">' . mb_strimwidth($description, 0, 100, '...') . '</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 mb-0 text-primary">' . $price . ' ₽</span>
                            <a href="/product/' . $id . '" class="btn btn-outline-primary btn-sm">Подробнее</a>
                        </div>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}