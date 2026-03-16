<?php
namespace App\Router;

use App\Controllers\ProductController;
use App\Controllers\HomeController;
use App\Views\AboutTemplate;

class Router
{
    public function route(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $pieces = explode('/', $path);
        $resource = $pieces[1] ?? '';
        
        switch ($resource) {
            case "product":
                $product = new ProductController();
                $id = ($pieces[2]) ? intval($pieces[2]) : 0;
                return $product->get($id);
                
            case "about":
                return AboutTemplate::getTemplate();
                
            case "":
            case "home":
                $home = new HomeController();
                return $home->get();
                
            default:
                return '<h1 class="container mt-5">404 - Страница не найдена</h1>';
        }
    }
}