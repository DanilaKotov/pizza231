<?php
namespace App\Controllers;

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
                    // 👇 Позже можно добавить: 'name', 'price', 'image' для отображения
                ];
            }
            
            // 👇 Флеш-сообщение (НОВОЕ)
            $_SESSION['flash'] = "Товар успешно добавлен в корзину!";
            $_SESSION['flash_type'] = "success";
        }
        
        // 👇 Редирект назад (НОВОЕ - вместо var_dump/exit)
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
        
        // 👇 Флеш-сообщение (НОВОЕ)
        $_SESSION['flash'] = "Корзина очищена!";
        $_SESSION['flash_type'] = "warning";
        
        // 👇 Редирект (НОВОЕ)
        $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$prevUrl}");
        exit();
    }
}