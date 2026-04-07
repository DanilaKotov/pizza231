<?php

declare(strict_types=1);

namespace App\Router;

// 👇 Подключения контроллеров
require_once __DIR__ . '/../Controllers/HomeController.php';
require_once __DIR__ . '/../Controllers/AboutController.php';
require_once __DIR__ . '/../Controllers/ProductController.php';
require_once __DIR__ . '/../Controllers/BasketController.php';
require_once __DIR__ . '/../Controllers/OrderController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';
require_once __DIR__ . '/../Controllers/ProfileController.php';
require_once __DIR__ . '/../Controllers/OrdersController.php';

use App\Controllers\HomeController;
use App\Controllers\AboutController;
use App\Controllers\ProductController;
use App\Controllers\BasketController;
use App\Controllers\OrderController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\OrdersController;

class Router
{
    /**
     * ID параметра из URL (для маршрутов типа /product/5)
     */
    private int $id = 0;

    /**
     * Основной метод маршрутизации
     */
    public function route(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $pieces = explode("/", $path);
        $resource = $pieces[1] ?? '';
        $this->id = isset($pieces[2]) ? intval($pieces[2]) : 0;
        $method = $_SERVER['REQUEST_METHOD'];

        $routes = $this->getRoutes();

        // Если маршрут не найден — возвращаем главную
        if (!isset($routes[$resource])) {
            return $this->handleDefault();
        }

        $route = $routes[$resource];

        // Обработка методов для ресурса (например, order: GET/POST)
        if (isset($route[$method])) {
            $route = $route[$method];
        }

        return $this->executeRoute($route, $pieces);
    }

    /**
     * Карта маршрутов (вместо большого switch)
     */
    private function getRoutes(): array
    {
        return [
            // Главная
            '' => [
                'controller' => HomeController::class,
                'method' => 'get',
            ],
            'home' => [
                'controller' => HomeController::class,
                'method' => 'get',
            ],

            // О нас
            'about' => [
                'controller' => AboutController::class,
                'method' => 'get',
            ],

            // Каталог товаров
            'products' => [
                'controller' => ProductController::class,
                'method' => 'get',
                'params' => ['id' => null],
            ],

            // Карточка товара
            'product' => [
                'controller' => ProductController::class,
                'method' => 'get',
                'params' => ['id' => $this->getId()],
            ],

            // Корзина: добавление
            'basket' => [
                'controller' => BasketController::class,
                'method' => 'add',
                'redirect' => true,
            ],

            // Корзина: очистка
            'basket-clear' => [
                'controller' => BasketController::class,
                'method' => 'clear',
                'redirect' => true,
            ],

            // 👇 Оформление заказа: ВСЕГДА вызываем get()
            // get() сам проверит $_SERVER['REQUEST_METHOD'] и вызовет create() внутри
            'order' => [
                'controller' => OrderController::class,
                'method' => 'get',
            ],

            // Регистрация: страница
            'register' => [
                'controller' => AuthController::class,
                'method' => 'register',
            ],

            // Авторизация: действия
            'auth' => [
                'register' => [
                    'controller' => AuthController::class,
                    'method' => 'registerSubmit',
                    'redirect' => true,
                ],
                'login' => [
                    'controller' => AuthController::class,
                    'method' => 'loginSubmit',
                    'redirect' => true,
                ],
                'logout' => [
                    'controller' => AuthController::class,
                    'method' => 'logout',
                    'redirect' => true,
                ],
            ],

            // Вход: страница
            'login' => [
                'controller' => AuthController::class,
                'method' => 'login',
            ],

            // Профиль
            'profile' => [
                'controller' => ProfileController::class,
                'method' => 'index',  // index() сам обрабатывает под-маршруты
            ],

            // Мои заказы
            'orders' => [
                'controller' => OrdersController::class,
                'method' => 'index',
            ],
            'orders/view' => [
                'controller' => OrdersController::class,
                'method' => 'view',
                'params' => ['orderId' => $this->getId()],
            ],
        ];
    }


    private function executeRoute(array $route, array $pieces): string
    {
        // Обработка вложенных маршрутов типа auth/register
        if (isset($pieces[2]) && isset($route[$pieces[2]])) {
            $route = $route[$pieces[2]];
        }

        // Создание контроллера
        $controller = new $route['controller']();

        // Подготовка параметров
        $params = [];
        if (isset($route['params'])) {
            $params = $route['params'];
            // Фильтруем null-параметры
            $params = array_filter($params, fn($v) => $v !== null);
        }

        // Вызов метода контроллера
        $result = $controller->{$route['method']}(...array_values($params));

        // Обработка редиректа
        if ($route['redirect'] ?? false) {
            $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$prevUrl}");
            return '';
        }

        return $result ?? '';
    }

    /**
     * Обработчик маршрута по умолчанию
     */
    private function handleDefault(): string
    {
        return (new HomeController())->get();
    }

    /**
     * Геттер для ID (используется в getRoutes)
     */
    private function getId(): int
    {
        return $this->id;
    }
}
