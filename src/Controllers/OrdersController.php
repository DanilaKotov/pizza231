<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

// 👇 ДОБАВИТЬ ЭТИ ДВЕ СТРОКИ:
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Views/OrdersTemplate.php';

class OrdersController
{
    /**
     * Страница заказов пользователя
     */
    public function index(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit();
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['id']);

        if (!$user) {
            header("Location: /login");
            exit();
        }

        // Получаем заказы пользователя
        $orders = $this->getUserOrders($user['email']);

        return \App\Views\OrdersTemplate::getOrdersTemplate($user, $orders);
    }

    /**
     * Детали заказа
     */
    public function view(int $orderId): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit();
        }

        $user = $_SESSION['user'];
        $ordersFile = __DIR__ . '/../../storage/order.json';

        if (!file_exists($ordersFile)) {
            header("Location: /orders");
            exit();
        }

        $data = file_get_contents($ordersFile);
        $orders = json_decode($data, true);

        if (!is_array($orders) || !isset($orders[$orderId])) {
            header("Location: /orders");
            exit();
        }

        $order = $orders[$orderId];

        // Проверка что заказ принадлежит пользователю
        if (($order['email'] ?? '') !== $user['email']) {
            header("Location: /orders");
            exit();
        }

        return \App\Views\OrdersTemplate::getOrderDetailTemplate($user, $order, $orderId);
    }

    /**
     * Получение заказов пользователя
     */
    private function getUserOrders(string $email): array
    {
        $ordersFile = __DIR__ . '/../../storage/order.json';

        if (!file_exists($ordersFile)) {
            return [];
        }

        $data = file_get_contents($ordersFile);
        $orders = json_decode($data, true);

        if (!is_array($orders)) {
            return [];
        }

        $userOrders = array_filter($orders, fn($order) => ($order['email'] ?? '') === $email);

        usort($userOrders, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return array_values($userOrders);
    }
}
