<?php
namespace App\Configs;

class Config
{
    // Пути к файлам данных
    const FILE_PRODUCTS = __DIR__ . '/../../storage/products.json';
    const FILE_ORDERS = __DIR__ . '/../../storage/order.json';
    const FILE_USERS = __DIR__ . '/../../storage/users.json';
    
    //  НОВОЕ: Типы хранилищ данных (для внедрения зависимостей)
    const TYPE_FILE = "file";
    const TYPE_DB = "db";
    
    // НОВОЕ: Активный тип хранилища (переключайте здесь)
    const STORAGE_TYPE = self::TYPE_FILE;
}