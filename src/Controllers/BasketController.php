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
        
        var_dump($_SESSION);
        exit();
            // 👇 Опционально: флеш-сообщение
            // $_SESSION['flash'] = "Товар добавлен в корзину!";
        }
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
        // Опционально: $_SESSION['flash'] = "Корзина очищена";
    }
}