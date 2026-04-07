<?php

declare(strict_types=1);

namespace App\Views;

class ProductTemplate extends BaseTemplate
{
    public static function getAllTemplate(array $arr): string
    {
        $str = '<div class="container py-5">';
        $str .= '<h1 class="text-center mb-4">Каталог продукции</h1>';

        // Кнопка "Фильтры" + модальное окно
        $str .= <<<LINE
            <div class="text-center mb-4">
                <button class="btn btn-primary btn-lg" id="btn-open-filters" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel me-2"></i>Фильтры
                    <span class="badge bg-warning text-dark ms-2" id="filter-count">0</span>
                </button>
            </div>

            <!-- Модальное окно с фильтрами -->
            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-white" id="filterModalLabel">
                                <i class="bi bi-funnel me-2"></i>Категории товаров
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="filter-list-vertical">
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="all" class="filter-checkbox">
                                    <span class="filter-label text-white">📦 Все товары</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="keyboard" class="filter-checkbox">
                                    <span class="filter-label text-white">⌨️ Клавиатуры</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="mouse" class="filter-checkbox">
                                    <span class="filter-label text-white">🖱️ Мыши</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="monitor" class="filter-checkbox">
                                    <span class="filter-label text-white">🖥️ Мониторы</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="headset" class="filter-checkbox">
                                    <span class="filter-label text-white">🎧 Гарнитуры</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="webcam" class="filter-checkbox">
                                    <span class="filter-label text-white">📹 Веб-камеры</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="gpu" class="filter-checkbox">
                                    <span class="filter-label text-white">🎮 Видеокарты</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="cpu" class="filter-checkbox">
                                    <span class="filter-label text-white">💻 Процессоры</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="ram" class="filter-checkbox">
                                    <span class="filter-label text-white">🔹 Оперативная память</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="storage" class="filter-checkbox">
                                    <span class="filter-label text-white">💾 Накопители</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="case" class="filter-checkbox">
                                    <span class="filter-label text-white">🖥️ Корпуса</span>
                                    <span class="filter-check"><i class="bi bi-check-circle-fill"></i></span>
                                </label>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-danger" id="btn-clear-filters">
                                    <i class="bi bi-x-circle me-1"></i>Сбросить все фильтры
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Закрыть
                            </button>
                            <button type="button" class="btn btn-primary" id="btn-apply-filters">
                                <i class="bi bi-check-circle me-2"></i>Применить (<span id="selected-count">0</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            LINE;

        // Контейнер для товаров
        $str .= '<div id="products-container" class="row g-4">';

        foreach ($arr as $item) {
            $name = htmlspecialchars($item['name'] ?? 'Без названия');
            $description = htmlspecialchars($item['description'] ?? '');
            $price = number_format($item['price'] ?? 0, 0, '.', ' ');
            $image = htmlspecialchars($item['image'] ?? '/assets/img/no-image.jpg');
            $id = (int) ($item['id'] ?? 0);
            $category = htmlspecialchars($item['category'] ?? 'other');

            $element_template = <<<END
                <div class="col-12 product-item" data-category="{$category}">
                    <div class="row mb-0 pb-3 border-bottom product-card">
                        <div class="col-md-4 col-lg-3">
                            <img src="{$image}"
                                 class="w-100 rounded shadow-sm"
                                 alt="{$name}"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=Нет+фото';">
                        </div>
                        <div class="col-md-8 col-lg-9">
                            <div class="ps-md-3 mt-3 mt-md-0">
                                <a href="/product/{$id}"><h3 class="fw-bold text-white mb-2">{$name}</h3></a>
                                <p class="text-muted mb-3">{$description}</p>
                                <div class="d-flex align-items-center flex-wrap gap-3">
                                    <h4 class="text-primary fw-bold mb-0">{$price} ₽</h4>
                                    <a href="/product/{$id}" class="btn btn-outline-primary btn-sm">Подробнее</a>
                                    <form action="/basket" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="{$id}">
                                        <button type="submit" class="btn btn-primary btn-sm">В корзину</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                END;

            $str .= $element_template;
        }
        $str .= "</div></div>";

        // JavaScript для фильтрации
        $str .= <<<JS
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.filter-checkbox');
                const filterOptions = document.querySelectorAll('.filter-option');
                const products = document.querySelectorAll('.product-item');
                const filterCount = document.getElementById('filter-count');
                const selectedCount = document.getElementById('selected-count');
                const btnApply = document.getElementById('btn-apply-filters');
                const btnClear = document.getElementById('btn-clear-filters');
                const filterModal = document.getElementById('filterModal');
                const checkboxAll = document.querySelector('input[value="all"]');

                function updateCounter() {
                    const checked = document.querySelectorAll('.filter-checkbox:checked:not([value="all"])');
                    const count = checked.length;

                    if (selectedCount) selectedCount.textContent = count;
                    if (filterCount) {
                        filterCount.textContent = count;
                        filterCount.style.display = count > 0 ? 'inline' : 'none';
                    }
                }

                // 🔥 Клик по ВСЕЙ кнопке фильтра
                filterOptions.forEach(option => {
                    option.addEventListener('click', function(e) {
                        const checkbox = this.querySelector('.filter-checkbox');
                        checkbox.checked = !checkbox.checked;

                        if (checkbox.value === 'all') {
                            if (checkbox.checked) {
                                checkboxes.forEach(cb => {
                                    if (cb.value !== 'all') {
                                        cb.checked = false;
                                        cb.closest('.filter-option').classList.remove('active');
                                    }
                                });
                                this.classList.add('active');
                            } else {
                                this.classList.remove('active');
                            }
                        } else {
                            if (checkbox.checked) {
                                checkboxAll.checked = false;
                                checkboxAll.closest('.filter-option').classList.remove('active');
                                this.classList.add('active');
                            } else {
                                this.classList.remove('active');
                            }
                        }

                        updateCounter();
                    });
                });

                btnApply.addEventListener('click', function() {
                    const selectedCategories = [];
                    checkboxes.forEach(cb => {
                        if (cb.checked && cb.value !== 'all') {
                            selectedCategories.push(cb.value);
                        }
                    });

                    let visibleCount = 0;

                    products.forEach(product => {
                        const category = product.getAttribute('data-category');

                        if (selectedCategories.length === 0 || checkboxAll.checked) {
                            product.style.display = 'block';
                            product.style.animation = 'fadeIn 0.3s ease';
                            visibleCount++;
                        } else if (selectedCategories.includes(category)) {
                            product.style.display = 'block';
                            product.style.animation = 'fadeIn 0.3s ease';
                            visibleCount++;
                        } else {
                            product.style.display = 'none';
                        }
                    });

                    filterCount.textContent = visibleCount;

                    const modalInstance = bootstrap.Modal.getInstance(filterModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });

                btnClear.addEventListener('click', function(e) {
                    e.stopPropagation();
                    checkboxes.forEach(cb => {
                        cb.checked = false;
                        cb.closest('.filter-option').classList.remove('active');
                    });
                    updateCounter();
                });

                updateCounter();
            });
            </script>
            JS;

        return parent::getTemplate($str);
    }

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
        $price = number_format((float) ($data['price'] ?? 0), 0, '.', ' ');
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
                                    <h2 class="card-title display-6 fw-bold text-white mb-3">' . $title . '</h2>
                                    <p class="card-text text-muted lead mb-4" style="line-height: 1.6;">
                                        ' . $description . '
                                    </p>
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="display-5 fw-bold text-primary me-3">' . $price . ' ₽</span>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">В наличии</span>
                                        </div>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                            <form action="/basket" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="' . (int) $data['id'] . '">
                                                <button type="submit" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                                         class="bi bi-cart-plus me-2" viewBox="0 0 16 16">
                                                        <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
                                                        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                                    </svg>
                                                    В корзину
                                                </button>
                                            </form>
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
