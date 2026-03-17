<?php
namespace App\Views;

class ProductTemplate extends BaseTemplate
{
    /**
     * Метод для отображения КАТАЛОГА товаров (список)
     */
    public static function getAllTemplate(array $arr): string 
    {
        $str = '<div class="container py-5">';
        $str .= '<h1 class="text-center mb-5">Каталог продукции</h1>';

        foreach ($arr as $item) {
            $name = htmlspecialchars($item['name'] ?? 'Без названия');
            $description = htmlspecialchars($item['description'] ?? '');
            $price = number_format($item['price'] ?? 0, 0, '.', ' ');
            $image = htmlspecialchars($item['image'] ?? '/assets/img/no-image.jpg');
            $id = (int)($item['id'] ?? 0);
            
            $element_template = <<<END
            <div class="row mb-4 pb-4 border-bottom">
                <div class="col-md-4 col-lg-3">
                    <img src="{$image}" 
                         class="w-100 rounded shadow-sm" 
                         alt="{$name}"
                         style="height: 200px; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Нет+фото';">
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="ps-md-3 mt-3 mt-md-0">
                        <a href="/product/{$id}"><h3 class="fw-bold text-dark mb-2">{$name}</h3></a>
                        <p class="text-muted mb-3">{$description}</p>
                        <div class="d-flex align-items-center">
                            <h4 class="text-primary fw-bold mb-0 me-3">{$price} ₽</h4>
                            <a href="/product/{$id}" class="btn btn-outline-primary btn-sm">Подробнее</a>
                        </div>
                    </div>
                </div>
            </div>
            END;

            $str .= $element_template;
        }
        $str .= "</div>";
        
        return parent::getTemplate($str);
    }

    /**
     * Метод для отображения ОДНОЙ карточки товара
     */
    public static function getCardTemplate($data): string
    {
        if (!$data) {
            return '
            <div class="container mt-5">
                <div class="alert alert-warning text-center shadow-sm" role="alert">
                    <h4 class="alert-heading">Товар не найден!</h4>
                    <p>К сожалению, товар с таким идентификатором отсутствует в нашем каталоге.</p>
                    <hr>
                    <a href="/product/1" class="btn btn-outline-warning">Попробовать товар №1</a>
                </div>
            </div>';
        }
        
        $image = $data['image'] ?? '';
        $fallbackImage = 'https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        
        $title = htmlspecialchars($data['name'] ?? 'Без названия');
        $description = htmlspecialchars($data['description'] ?? 'Описание отсутствует.');
        $price = number_format((float)($data['price'] ?? 0), 0, '.', ' ');
        
        $finalImage = (!empty($image)) ? $image : $fallbackImage;

        return parent::getTemplate('
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                        <div class="row g-0 h-100">
                            <div class="col-md-5 col-lg-4 bg-light d-flex align-items-center justify-content-center p-4" style="min-height: 200px;">
                                <img src="' . $finalImage . '" 
                                     class="img-fluid rounded-3 shadow-sm" 
                                     alt="' . $title . '" 
                                     style="max-height: 200px; width: 100%; object-fit: contain;"
                                     onerror="this.src=\'' . $fallbackImage . '\'; this.onerror=null;">
                            </div>
                            <div class="col-md-7 col-lg-8">
                                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center">
                                    <h5 class="text-uppercase text-secondary fw-bold ls-1 mb-2" style="font-size: 0.9rem;">Наш каталог</h5>
                                    <h2 class="card-title display-6 fw-bold text-dark mb-3">' . $title . '</h2>
                                    <p class="card-text text-muted lead mb-4" style="line-height: 1.6;">
                                        ' . $description . '
                                    </p>
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="display-5 fw-bold text-primary me-3">' . $price . ' ₽</span>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">В наличии</span>
                                        </div>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                            <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart-plus me-2" viewBox="0 0 16 16">
                                                    <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
                                                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                                </svg>
                                                В корзину
                                            </button>
                                            <a href="/" class="btn btn-outline-secondary btn-lg px-4">На главную</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>');
    }
}