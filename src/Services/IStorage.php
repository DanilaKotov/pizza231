<?php

declare(strict_types=1);

namespace App\Services;

interface IStorage
{
    public function loadData(string $name): ?array;
    public function saveData(string $name, array $data): bool;
}
