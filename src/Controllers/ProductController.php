<?php
namespace App\Controllers;

use App\Models\Product;
use App\Views\ProductTemplate;

class ProductController
{
    public function get($id): string 
    {
        $model = new Product();
        $products = $model->loadData();
        
        // Ищем товар по id, а не по индексу массива
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return ProductTemplate::getCardTemplate($product);
            }
        }
        
        // Товар не найден
        return ProductTemplate::getCardTemplate(null);
    }
}