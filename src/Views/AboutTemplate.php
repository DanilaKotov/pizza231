<?php
namespace App\Views;

use App\Views\BaseTemplate;

class AboutTemplate extends BaseTemplate
{
    public static function getTemplate(string $content = ''): string
    {
        $ourContent = '
        <div class="container mt-4">
            <h1 class="text-center mb-4">О магазине «Пиксель»</h1>
            
            <div class="row">
                <div class="col-md-8">
                    <p class="lead">
                        «Пиксель» — ваш надёжный партнёр в мире компьютерных технологий и комплектующих.
                    </p>
        
                    
                    <p>
                        В нашем ассортименте только <strong>оригинальная продукция</strong> от ведущих мировых брендов: 
                        ASUS, MSI, NVIDIA, AMD, Intel, Corsair и других. 
                        Все товары имеют официальную гарантию производителя.
                    </p>
                    
                    <h4 class="mt-4">Наши преимущества</h4>
                    <ul class="list-unstyled">
                        <li>✅ Только сертифицированные товары</li>
                        <li>✅ Профессиональная консультация перед покупкой</li>
                        <li>✅ Сборка ПК любой сложности под заказ</li>
                        <li>✅ Доставка по Кемерово и области</li>
                        <li>✅ Гибкая система скидок и акций</li>
                    </ul>
                
                </div>
                
                <div class="col-md-4">
                    <!-- Карта Яндекс -->
                    <div class="card">
                        <div class="card-body p-0">
                            <script type="text/javascript" charset="utf-8" 
                                    async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3AYOUR_CONSTRUCTOR_ID&amp;width=100%25&amp;height=300&amp;lang=ru_RU&amp;scroll=true">
                            </script>
                            <!-- Замените YOUR_CONSTRUCTOR_ID на ID вашей карты из Конструктора Яндекс.Карт -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        
        return parent::getTemplate($ourContent);
    }
}