<?php
namespace Test;

use PHPUnit\Framework\TestCase;
use App\Models\Product;

class ProductTest extends TestCase
{
    /**
     * Тест-1: Проверка бизнес-логики (правильность вычислений)
     * 
     * Заказ: 2 товара по 350₽ + 1 товар по 500₽ = 1200₽
     * (исправлено: 2×350 + 1×500 = 700 + 500 = 1200, а не 1300)
     */
    public function testPrepareDataCalculatesTotalCorrectly(): void
    {
        $model = new Product();
        
        // Данные формы
        $formData = [
            'fio' => 'Иванов Иван Иванович',
            'address' => 'г. Кемерово, ул. Советская, д. 10',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@example.ru'
        ];
        
        // Товары из корзины: 2×350 + 1×500 = 1200
        $basketData = [
            [
                'id' => 1,
                'name' => 'Товар 1',
                'price' => 350,
                'quantity' => 2
            ],
            [
                'id' => 2,
                'name' => 'Товар 2',
                'price' => 500,
                'quantity' => 1
            ]
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
        $model = new Product();
        
        // Данные формы с потенциально опасным кодом
        $formData = [
            'fio' => '<script>alert("XSS")</script>Иванов',
            'address' => '"><img src=x onerror=alert(1)>',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'test@example.ru'
        ];
        
        $basketData = [
            ['id' => 1, 'name' => 'Тест', 'price' => 100, 'quantity' => 1]
        ];
        
        $result = $model->prepareData($formData, $basketData);
        
        // ✅ Проверяем, что HTML-теги экранированы
        $this->assertStringNotContainsString('<script>', $result['fio'], 'Тег <script> должен быть экранирован');
        $this->assertStringContainsString('&lt;script&gt;', $result['fio'], 'Теги должны быть преобразованы в HTML-сущности');
        
        // ✅ Проверяем, что теги <img> экранированы (превращены в текст)
        $this->assertStringNotContainsString('<img', $result['address'], 'Тег <img> должен быть экранирован');
        $this->assertStringContainsString('&lt;img', $result['address'], 'Тег <img> должен быть преобразован в &lt;img');
        
        // ✅ Проверяем, что кавычки экранированы
        $this->assertStringContainsString('&quot;', $result['address'], 'Кавычки должны быть преобразованы в &quot;');
        
        // ✅ Проверяем, что результат безопасен для вывода в HTML
        $this->assertEquals(
            htmlspecialchars($formData['fio'], ENT_QUOTES, 'UTF-8'),
            $result['fio'],
            'ФИО должно быть полностью экранировано через htmlspecialchars()'
        );
    }
    
    /**
     * Тест-3: Проверка обработки пустых данных
     */
    public function testPrepareDataHandlesEmptyFields(): void
    {
        $model = new Product();
        
        $formData = [
            'fio' => '',
            'address' => '',
            'phone' => '',
            'email' => ''
        ];
        
        $basketData = [];
        
        $result = $model->prepareData($formData, $basketData);
        
        $this->assertEquals('', $result['fio']);
        $this->assertEquals(0, $result['all_sum']);
        $this->assertIsArray($result['products']);
        $this->assertArrayHasKey('created_at', $result);
    }
}