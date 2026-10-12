<?php
namespace App\Configs;

class Config
{
    // Пути к файлам данных
    const FILE_PRODUCTS = __DIR__ . '/../../storage/products.json';
    const FILE_ORDERS = __DIR__ . '/../../storage/order.json';
    const FILE_USERS = __DIR__ . '/../../storage/users.json';
    
    // 👇 Настройки подключения к БД
    const MYSQL_DSN = 'mysql:dbname=is231;host=localhost';
    const MYSQL_USER = 'root';
    const MYSQL_PASSWORD = '';
    
    // 👇 Имена таблиц
    const TABLE_PRODUCTS = "products";
    const TABLE_ORDERS = "orders";
    const TABLE_USERS = "users";  // ← ДОБАВЛЕНО
    
    // 👇 Типы хранилищ данных
    const TYPE_FILE = "file";
    const TYPE_DB = "db";
    
    // 👇 Активный тип хранилища
    const STORAGE_TYPE = self::TYPE_DB;
}