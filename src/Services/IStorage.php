<?php
namespace App\Services;

// 👇 НОВОЕ: Подключаем разделённые интерфейсы
require_once __DIR__ . '/ILoadStorage.php';
require_once __DIR__ . '/ISaveStorage.php';

interface IStorage extends ILoadStorage, ISaveStorage
{
}