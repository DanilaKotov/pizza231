<?php
namespace App\Models;

require_once __DIR__ . '/../Configs/Config.php';

use App\Configs\Config;
use App\Services\ILoadStorage;  // 👇 ИЗМЕНЕНО: IStorage → ILoadStorage

class Product
{
    private ILoadStorage $dataStorage;  // 👇 ИЗМЕНЕНО: тип интерфейса
    private string $nameResource;
    
    public function __construct(ILoadStorage $service, string $name)  // 👇 ИЗМЕНЕНО
    {
        $this->dataStorage = $service;
        $this->nameResource = $name;
    }
 
    public function loadData(): ?array {
        return $this->dataStorage->loadData($this->nameResource); 
    }

    public function saveData($arr): bool {
        // Для Product сохранение не требуется (только чтение)
        return false;
    }
    
    public function getBasketData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['basket'])) {
            $_SESSION['basket'] = [];
        }
        $products = $this->loadData();
        $basketProducts = [];
        if (!$products) {
            return [];
        }
        foreach ($products as $product) {
            $id = (int)$product['id'];
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
    
    public function prepareData(array $formData, array $products): array
    {
        $arr = [];
        $arr['fio'] = htmlspecialchars(trim($formData['fio'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['address'] = htmlspecialchars(trim($formData['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['phone'] = htmlspecialchars(trim($formData['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['email'] = htmlspecialchars(trim($formData['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $arr['created_at'] = date("d-m-Y H:i:s");
        $arr['products'] = $products;
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