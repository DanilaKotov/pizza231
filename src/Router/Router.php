<?php
namespace App\Router;

require_once __DIR__ . '/../Controllers/HomeController.php';
require_once __DIR__ . '/../Controllers/AboutController.php';
require_once __DIR__ . '/../Controllers/ProductController.php';
require_once __DIR__ . '/../Controllers/BasketController.php'; // 👈

use App\Controllers\HomeController;
use App\Controllers\AboutController;
use App\Controllers\ProductController;
use App\Controllers\BasketController; // 👈

class Router
{
    public function route(string $url): ?string 
    {
        $path = parse_url($url, PHP_URL_PATH);
        $pieces = explode("/", $path);
        $resource = $pieces[1] ?? '';

        switch ($resource) {
            case "about":
                $controller = new AboutController();
                return $controller->get();
            
            case "home":
            case "":
                $controller = new HomeController();
                return $controller->get();

            case "products":
                $productController = new ProductController();
                return $productController->get(null);
                
            case "product":
                $productController = new ProductController();
                $id = isset($pieces[2]) ? intval($pieces[2]) : null;
                return $productController->get($id);
            
            case "basket":
                $basketController = new BasketController();
                $basketController->add();
                $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
                header("Location: {$prevUrl}");
                return "";

            case "basket-clear":
                $basketController = new BasketController();
                $basketController->clear();
                $prevUrl = $_SERVER['HTTP_REFERER'] ?? '/';
                header("Location: {$prevUrl}");
                return "";
                
            default:
                $controller = new HomeController();
                return $controller->get();
        }
    }
}