<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use App\Models\Product;
use App\Services\FileStorage;
use App\Configs\Config;

require_once __DIR__ . '/../src/Configs/Config.php';
require_once __DIR__ . '/../src/Services/IStorage.php';
require_once __DIR__ . '/../src/Services/FileStorage.php';
require_once __DIR__ . '/../src/Models/Product.php';

class ProductTest extends TestCase
{
    /**
     * Создание тестовой модели Product
     */
    private function createTestModel(string $resource = ''): Product
    {
        $storage = new FileStorage();
        return new Product($storage, $resource);
    }

    /**
     * Тест-1: Проверка бизнес-логики (правильность вычислений)
     *
     * Заказ: 2 товара по 350₽ + 1 товар по 500₽ = 1200₽
     */
    public function testPrepareDataCalculatesTotalCorrectly(): void
    {
        $model = $this->createTestModel();

        // Данные формы
        $formData = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Кемерово, ул. Советская, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.ru',
        ];

        // Товары из корзины: 2×350 + 1×500 = 1200
        $basketData = [
            [
                'id' => 1,
                'name' => 'Товар 1',
                'price' => 350,
                'quantity' => 2,
            ],
            [
                'id' => 2,
                'name' => 'Товар 2',
                'price' => 500,
                'quantity' => 1,
            ],
        ];

        // Вызываем метод
        $result = $model->prepareData($formData, $basketData);

        // Проверяем сумму
        $this->assertEquals(1200, $result['all_sum'], 'Общая сумма заказа должна быть 1200 рублей');

        // Проверяем санитизацию данных
        $this->assertEquals('Иванов Иван Иванович', $result['fio']);
        $this->assertEquals('ivanov@example.ru', $result['email']);

        // Проверяем наличие товаров
        $this->assertCount(2, $result['products']);
        $this->assertEquals('Товар 1', $result['products'][0]['name']);
    }

    /**
     * Тест-2: Проверка защиты от XSS-инъекций
     */
    public function testPrepareDataSanitizesXSS(): void
    {
        $model = $this->createTestModel();

        // Данные формы с потенциально опасным кодом
        $formData = [
            'fio' => '<script>alert("XSS")</script>Иванов',
            'address' => '"><img src=x onerror=alert(1)>',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'test@example.ru',
        ];

        $basketData = [
            ['id' => 1, 'name' => 'Тест', 'price' => 100, 'quantity' => 1],
        ];

        $result = $model->prepareData($formData, $basketData);

        // ✅ Проверяем, что HTML-теги экранированы
        $this->assertStringNotContainsString('<script>', $result['fio'], 'Тег <script> должен быть экранирован');
        $this->assertStringContainsString('&lt;script&gt;', $result['fio'], 'Теги должны быть преобразованы в HTML-сущности');

        // ✅ Проверяем, что теги <img> экранированы
        $this->assertStringNotContainsString('<img', $result['address'], 'Тег <img> должен быть экранирован');
        $this->assertStringContainsString('&lt;img', $result['address'], 'Тег <img> должен быть преобразован в &lt;img');

        // ✅ Проверяем, что кавычки экранированы
        $this->assertStringContainsString('&quot;', $result['address'], 'Кавычки должны быть преобразованы в &quot;');
    }

    /**
     * Тест-3: Проверка обработки пустых данных
     */
    public function testPrepareDataHandlesEmptyFields(): void
    {
        $model = $this->createTestModel();

        $formData = [
            'fio' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
        ];

        $basketData = [];

        $result = $model->prepareData($formData, $basketData);

        $this->assertEquals('', $result['fio']);
        $this->assertEquals(0, $result['all_sum']);
        $this->assertIsArray($result['products']);
        $this->assertArrayHasKey('created_at', $result);
    }
}
