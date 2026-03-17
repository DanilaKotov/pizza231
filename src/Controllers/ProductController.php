<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Views/ProductTemplate.php';

use App\Models\Product; 
use App\Views\ProductTemplate;

class ProductController
{
    public function get(?int $id = null): string {
    $model = new Product();
    $data = $model->loadData();
    
    //  Если ID не задан — показываем каталог
    if (!isset($id) || $id === null) {
        return ProductTemplate::getAllTemplate($data);
    }
    
    //  Если ID задан — показываем карточку товара
    $product = null;
    foreach ($data as $item) {
        if ($item['id'] == $id) {
            $product = $item;
            break;
        }
    }
    return ProductTemplate::getCardTemplate($product);
}
}  