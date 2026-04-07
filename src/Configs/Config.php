<?php

declare(strict_types=1);

namespace App\Configs;

class Config
{
    // Пути к файлам данных
    public const FILE_PRODUCTS = __DIR__ . '/../../storage/products.json';
    public const FILE_ORDERS = __DIR__ . '/../../storage/order.json';
    public const FILE_USERS = __DIR__ . '/../../storage/users.json';

    //  НОВОЕ: Типы хранилищ данных (для внедрения зависимостей)
    public const TYPE_FILE = "file";
    public const TYPE_DB = "db";

    // НОВОЕ: Активный тип хранилища (переключайте здесь)
    public const STORAGE_TYPE = self::TYPE_FILE;
}
