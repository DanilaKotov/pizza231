<?php
namespace App\Services;

class FileStorage implements IStorage
{
    public function loadData(string $name): ?array
    {       
        if (!file_exists($name)) {
            return null;
        }

        $data = file_get_contents($name);
        $arr = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr)) {
            return null;
        }

        $indexedData = [];
        foreach ($arr as $item) {
            if (isset($item['id'])) {
                $indexedData[$item['id']] = $item;
            }
        }

        return $indexedData; 
    }

    public function saveData(string $name, array $arr): bool
    {
        $dir = dirname($name);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $allRecords = [];
        if (file_exists($name) && filesize($name) > 0) {
            $data = file_get_contents($name);
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $allRecords = $decoded;
            }
        }
        
        $allRecords[] = $arr;
        
        $json = json_encode($allRecords, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $result = file_put_contents($name, $json);
        
        return $result !== false;
    }
}