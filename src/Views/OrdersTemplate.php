<?php

declare(strict_types=1);

namespace App\Views;

class OrdersTemplate extends BaseTemplate
{
    /**
     * Список заказов
     */
    public static function getOrdersTemplate(array $user, array $orders): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Пользователь');
        $userEmail = htmlspecialchars($user['email'] ?? '');
        $userAvatar = $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'U') . '&background=667eea&color=fff&size=150';

        $content = <<<HTML
                    <div class="container py-5">
                        <div class="row mb-4">
                            <div class="col-12">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/profile" class="text-decoration-none">Профиль</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Мои заказы</li>
                                    </ol>
                                </nav>
                                <h1 class="mb-3">📦 Мои заказы</h1>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 mb-4">
                                <div class="card shadow-lg border-0">
                                    <div class="card-body text-center p-4">
                                        <img src="{$userAvatar}"
                                             alt="Аватар"
                                             class="rounded-circle shadow mb-3"
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                        <h4 class="mb-1">{$userName}</h4>
                                        <p class="text-muted mb-3">{$userEmail}</p>
                                        <div class="d-grid gap-2">
                                            <a href="/profile" class="btn btn-outline-primary">
                                                <i class="bi bi-person me-2"></i>Профиль
                                            </a>
                                            <a href="/" class="btn btn-outline-secondary">
                                                <i class="bi bi-house me-2"></i>На главную
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
            HTML;

        if (empty($orders)) {
            $content .= <<<HTML
                                    <div class="card shadow-lg border-0">
                                        <div class="card-body text-center py-5">
                                            <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                                            <h4 class="text-muted">У вас пока нет заказов</h4>
                                            <p class="text-muted">Оформите свой первый заказ в нашем магазине!</p>
                                            <a href="/products" class="btn btn-primary btn-lg mt-3">
                                                <i class="bi bi-cart3 me-2"></i>Перейти к покупкам
                                            </a>
                                        </div>
                                    </div>
                HTML;
        } else {
            $content .= '<div class="row g-4">';

            foreach ($orders as $index => $order) {
                $orderNum = count($orders) - $index;
                $date = date('d.m.Y H:i', strtotime($order['created_at']));
                $sum = number_format($order['all_sum'] ?? 0, 0, '.', ' ');
                $productsCount = count($order['products'] ?? []);
                $fio = htmlspecialchars($order['fio'] ?? 'Не указано');
                $address = htmlspecialchars($order['address'] ?? 'Не указано');

                $content .= <<<HTML
                                    <div class="col-12">
                                        <div class="card shadow-sm border-0 order-card">
                                            <div class="card-header bg-gradient-primary text-white">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-0"><i class="bi bi-box me-2"></i>Заказ #{$orderNum}</h5>
                                                        <small class="opacity-75">{$date}</small>
                                                    </div>
                                                    <span class="badge bg-success bg-opacity-75">
                                                        <i class="bi bi-check-circle me-1"></i>Выполнен
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <h6 class="text-muted mb-2"><i class="bi bi-person me-1"></i>Получатель</h6>
                                                        <p class="mb-0"><strong>{$fio}</strong></p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <h6 class="text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>Адрес доставки</h6>
                                                        <p class="mb-0"><strong>{$address}</strong></p>
                                                    </div>
                                                </div>

                                                <hr class="my-3">

                                                <h6 class="text-muted mb-3"><i class="bi bi-list-ul me-1"></i>Товары в заказе ({$productsCount} шт.)</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Товар</th>
                                                                <th class="text-center">Кол-во</th>
                                                                <th class="text-end">Цена</th>
                                                                <th class="text-end">Сумма</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                    HTML;

                foreach ($order['products'] as $product) {
                    $productName = htmlspecialchars($product['name'] ?? 'Товар');
                    $productPrice = number_format($product['price'] ?? 0, 0, '.', ' ');
                    $productQty = $product['quantity'] ?? 1;
                    $productSum = $product['sum'] ?? 0;

                    $content .= <<<HTML
                                                                <tr>
                                                                    <td>{$productName}</td>
                                                                    <td class="text-center">{$productQty}</td>
                                                                    <td class="text-end">{$productPrice} ₽</td>
                                                                    <td class="text-end"><strong>{$productSum} ₽</strong></td>
                                                                </tr>
                        HTML;
                }

                $content .= <<<HTML
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="d-flex justify-content-end mt-3">
                                                    <div class="text-end">
                                                        <p class="text-muted mb-1">Итого:</p>
                                                        <h4 class="text-primary mb-0"><strong>{$sum} ₽</strong></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-light">
                                                <a href="/orders/view/{$index}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye me-1"></i>Подробнее
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                    HTML;
            }

            $content .= '</div>';
        }

        $content .= <<<HTML
                            </div>
                        </div>
                    </div>
            HTML;

        return parent::getTemplate($content);
    }

    /**
     * Детали заказа
     */
    public static function getOrderDetailTemplate(array $user, array $order, int $orderId): string
    {
        return self::getOrdersTemplate($user, [$order]);
    }
}
