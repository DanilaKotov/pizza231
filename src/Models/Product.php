<?php
namespace App\Models;

require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/IStorage.php';

use App\Configs\Config;
use App\Services\IStorage;

class Product
{
    /**
     * 👇 НОВОЕ: Сервис для работы с данными (абстракция)
     */
    private IStorage $dataStorage;
    
    /**
     * 👇 НОВОЕ: Имя ресурса (путь к файлу или имя таблицы)
     */
    private string $nameResource;
    
    /**
     * 👇 НОВОЕ: Конструктор с внедрением зависимости
     * @param IStorage $service Сервис хранения данных
     * @param string $name Имя ресурса
     */
    public function __construct(IStorage $service, string $name)
    {
        $this->dataStorage = $service;
        $this->nameResource = $name;
    }
    
    /**
     * 👇 ИЗМЕНЕНО: Делегируем загрузку сервису хранения
     */
    public function loadData(): ?array
    {
        return $this->dataStorage->loadData($this->nameResource); 
    }
    
    /**
     * Возвращает товары, добавленные в корзину
     */
    public function getBasketData(): array
    {
        // Запускаем сессию, если ещё не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Инициализируем корзину, если нет
        if (!isset($_SESSION['basket'])) {
            $_SESSION['basket'] = [];
        }
        
        // Загружаем все товары
        $products = $this->loadData();
        $basketProducts = [];
        
        // Если товаров нет — возвращаем пустой массив
        if (!$products) {
            return [];
        }
        
        // Перебираем все товары и выбираем те, что в корзине
        foreach ($products as $product) {
            $id = (int)$product['id'];
            
            // Если товар есть в сессии корзины
            if (array_key_exists($id, $_SESSION['basket'])) {
                $quantity = $_SESSION['basket'][$id]['quantity'];
                $name = $product['name'];
                $price = (float)$product['price'];
                $image = $product['image'] ?? '/assets/img/no-image.jpg';
                $sum = $price * $quantity;
                
                $basketProducts[] = [
                    'id' => $id,
                    'name' => $name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'sum' => $sum,
                    'image' => $image
                ];
            }
        }
        
        return $basketProducts;
    }
    
    /**
     * 👇 ИЗМЕНЕНО: Делегируем сохранение сервису хранения + возвращаем bool
     */
    public function saveData(array $order): bool
    {
        return $this->dataStorage->saveData($this->nameResource, $order); 
    }
    
    /**
     * Подготавливает данные заказа для сохранения
     */
    public function prepareData(array $formData, array $products): array
    {
        $arr = [];
        
        // 🔐 Санитизация данных формы (защита от XSS-инъекций)
        $arr['fio'] = htmlspecialchars(trim($formData['fio'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['address'] = htmlspecialchars(trim($formData['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['phone'] = htmlspecialchars(trim($formData['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['email'] = htmlspecialchars(trim($formData['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // 📅 Дата создания заказа
        $arr['created_at'] = date("d-m-Y H:i:s");
        
        // 📦 Товары из корзины
        $arr['products'] = $products;
        
        // 💰 Бизнес-логика: подсчёт общей суммы заказа
        $all_sum = 0;
        foreach ($products as $product) {
            $price = (float)($product['price'] ?? 0);
            $quantity = (int)($product['quantity'] ?? 1);
            $all_sum += $price * $quantity;
        }
        $arr['all_sum'] = $all_sum;
        
        return $arr;
    }
}