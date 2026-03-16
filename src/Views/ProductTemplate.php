<?php
namespace App\Views;

class ProductTemplate extends BaseTemplate
{
    public static function getCardTemplate($data): string 
    {
        if (!$data) {
            return '<div class="container mt-5"><div class="alert alert-warning">Товар не найден</div></div>';
        }
        
        $template = parent::getTemplate();
        $title = $data['name'];
        
        $content = <<<HTML
        <div class="container mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card border-primary shadow" style="border-width: 3px;">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <div class="p-4 bg-light text-center" style="min-height: 350px; display: flex; align-items: center; justify-content: center;">
                                    <img src="/assets/images/{$data['image']}" 
                                         class="img-fluid" 
                                         alt="{$data['name']}"
                                         onerror="this.src='https://via.placeholder.com/400x400?text=Нет+фото'">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="card-body p-4">
                                    <h3 class="card-title text-primary mb-3">{$data['name']}</h3>
                                    <p class="card-text text-muted mb-4" style="line-height: 1.6;">{$data['description']}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <h2 class="text-success mb-1">{$data['price']} ₽</h2>
                                            <small class="text-muted">✓ В наличии</small>
                                        </div>
                                        <button class="btn btn-primary btn-lg px-4">
                                            🛒 В корзину
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/" class="btn btn-outline-secondary">
                            ← Назад к каталогу
                        </a>
                    </div>
                </div>
            </div>
        </div>
        HTML;

        return sprintf($template, $title, $content);
    }
}