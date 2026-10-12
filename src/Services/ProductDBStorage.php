<?php
namespace App\Services;

use PDO;

class ProductDBStorage extends DBStorage implements ILoadStorage
{
    public function loadData(string $name): ?array
    {
        // 👇 ИСПРАВЛЕНО: id_product AS id + category (для работы фильтров)
        $sql = "SELECT id_product AS id, name, description, image, price, category, created, updated FROM products";
        $result = $this->connection->query($sql, PDO::FETCH_ASSOC);
        $rows = $result->fetchAll();
        
        // Индексируем массив по id для совместимости
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['id']] = $row;
        }
        return $indexed;
    }
}