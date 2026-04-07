<?php

declare(strict_types=1);

namespace App\Views;

class OrderTemplate extends BaseTemplate
{
    public static function getOrderTemplate(array $products): string
    {
        $all_sum = 0;
        $content = '';

        $content .= '<div class="container py-5">';
        $content .= '<h1 class="text-center mb-5">Создание заказа</h1>';
        $content .= '<h3 class="mb-4">Корзина</h3>';

        if (empty($products)) {
            $content .= <<<LINE
                <div class="alert alert-info text-center">- нет добавленных товаров -</div>
                <div class="text-center mt-4">
                    <a href="/products" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart3 me-2"></i>Перейти к покупкам
                    </a>
                </div>
                LINE;
        } else {
            // Таблица товаров с видимыми линиями
            $content .= '<div class="cart-table-wrapper mb-4">';

            foreach ($products as $index => $product) {
                $name = htmlspecialchars($product['name']);
                $price = number_format((float) $product['price'], 0, '.', ' ');
                $quantity = (int) $product['quantity'];
                $sum = $product['sum'];
                $all_sum += $sum;

                $borderClass = '';
                if ($index === 0) {
                    $borderClass = 'cart-item-first';
                } elseif ($index === count($products) - 1) {
                    $borderClass = 'cart-item-last';
                } else {
                    $borderClass = 'cart-item-middle';
                }

                $content .= <<<LINE
                    <div class="cart-item {$borderClass}">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">{$name}</h5>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted d-block">Цена:</span>
                                <strong>{$price} ₽</strong>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted d-block">Количество:</span>
                                <strong>{$quantity} ед.</strong>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="text-muted d-block">Сумма:</span>
                                <h5 class="text-primary mb-0">{$sum} ₽</h5>
                            </div>
                        </div>
                    </div>
                    LINE;
            }

            $content .= '</div>';

            // Итого
            $formatted_sum = number_format($all_sum, 0, '.', ' ');
            $content .= <<<LINE
                <div class="cart-total-block text-end mb-4">
                    <h4>Итого: <span class="text-primary">{$formatted_sum} ₽</span></h4>
                </div>
                LINE;

            // Кнопка "Оформить заказ"
            $content .= <<<LINE
                <div class="text-end mb-4">
                    <button type="button" class="btn btn-success btn-lg" id="btn-show-delivery">
                        <i class="bi bi-cart-check me-2"></i>Оформить заказ
                    </button>
                </div>

                <!-- Форма доставки (скрыта по умолчанию) -->
                <div class="delivery-form-wrapper d-none" id="delivery-form-section">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Данные доставки</h4>
                        </div>
                                            <div class="card-body">
                            <form action="/order" method="POST" id="order-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fio" class="form-label">Ваше ФИО *</label>
                                        <input type="text" class="form-control" id="fio" name="fio"
                                               required placeholder="">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Телефон *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                               required placeholder="">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope me-1"></i>Электронная почта *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               required placeholder="">
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Адрес доставки *</label>
                                        <input type="text" class="form-control" id="address" name="address"
                                               required placeholder="">
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-3 justify-content-end">
                                    <a href="/products" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-arrow-left me-2"></i>Продолжить покупки
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>Подтвердить заказ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Кнопка очистки корзины -->
                <div class="text-end mt-3">
                    <form action="/basket-clear" method="POST">
                        <button type="submit" class="btn btn-danger btn-lg"
                                onclick="return confirm('Вы уверены, что хотите очистить корзину?')">
                            <i class="bi bi-trash3 me-2"></i>Очистить корзину
                        </button>
                    </form>
                </div>

                <!-- JavaScript для показа формы -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const btnShow = document.getElementById('btn-show-delivery');
                    const formSection = document.getElementById('delivery-form-section');

                    if (btnShow && formSection) {
                        btnShow.addEventListener('click', function() {
                            formSection.classList.remove('d-none');
                            formSection.style.opacity = '0';
                            formSection.style.transform = 'translateY(-10px)';
                            formSection.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                            setTimeout(function() {
                                formSection.style.opacity = '1';
                                formSection.style.transform = 'translateY(0)';
                            }, 10);

                            btnShow.style.display = 'none';
                            formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                });
                </script>
                LINE;
        }

        $content .= '</div>';
        return parent::getTemplate($content);
    }
}
