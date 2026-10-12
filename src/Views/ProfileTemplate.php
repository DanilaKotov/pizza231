<?php

declare(strict_types=1);

namespace App\Views;

class ProfileTemplate extends BaseTemplate
{
    public static function getProfileTemplate(array $user, array $orders): string
    {
        $avatarUrl = $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? $user['username'] ?? 'U') . '&background=667eea&color=fff&size=150';
        // Добавляем cache-busting параметр для локальных аватарок, чтобы браузер не кэшировал старое изображение
        if (!str_starts_with($avatarUrl, 'https://')) {
            $avatarUrl .= '?t=' . time();
        }
        $avatar = htmlspecialchars($avatarUrl);
        $name = htmlspecialchars($user['name'] ?? $user['username'] ?? 'Пользователь');
        $email = htmlspecialchars($user['email'] ?? '');
        $phone = htmlspecialchars($user['phone'] ?? '');
        $address = htmlspecialchars($user['address'] ?? '');
        $createdAt = !empty($user['created_at']) 
            ? date('d.m.Y', strtotime($user['created_at'])) 
            : date('d.m.Y');

        $content = '<div class="container py-5">
                        <div class="row">
                            <!-- Левая колонка - Информация о пользователе -->
                            <div class="col-lg-4 mb-4">
                                <div class="card shadow-lg border-0 profile-card">
                                    <div class="card-body text-center p-4">
                                        <div class="position-relative d-inline-block mb-3">
                                            <img src="' . $avatar . '" alt="Аватар"
                                                 class="rounded-circle shadow-lg"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #667eea;"
                                                 id="avatar-preview">
                                            <label for="avatar-upload" class="position-absolute bottom-0 end-0
                                                     btn btn-primary btn-sm rounded-circle shadow"
                                                   style="width: 45px; height: 45px; display: flex;
                                                          align-items: center; justify-content: center; cursor: pointer; border: 3px solid #fff;">
                                                <i class="bi bi-camera"></i>
                                            </label>
                                            <form id="avatar-form" action="/profile/upload-avatar" method="POST"
                                                  enctype="multipart/form-data">
                                                <input type="file" name="avatar" id="avatar-upload" accept="image/*"
                                                       onchange="this.form.submit()" class="d-none">
                                            </form>
                                        </div>
                                        <div id="avatar-upload-status" class="mt-2"></div>
                                        <h3 class="mb-1 fw-bold">' . $name . '</h3>
                                        <p class="text-muted mb-2"><i class="bi bi-envelope me-1"></i>' . $email . '</p>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            Регистрация: ' . $createdAt . '
                                        </p>
                                        <div class="d-grid gap-2">
                                            <a href="/orders" class="btn btn-primary">
                                                <i class="bi bi-box me-2"></i>Мои заказы
                                            </a>
                                            <a href="/auth/logout" class="btn btn-outline-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i>Выйти
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Правая колонка - Редактирование профиля и заказы -->
                            <div class="col-lg-8">
                                <!-- Редактирование профиля -->
                                <div class="card shadow-lg border-0 mb-4">
                                    <div class="card-header bg-gradient-primary text-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Редактирование профиля</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <form id="profile-edit-form">
                                            <input type="hidden" name="name" id="input-name">
                                            <input type="hidden" name="phone" id="input-phone">
                                            <input type="hidden" name="address" id="input-address">
                                            
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="name" class="form-label fw-semibold">Имя *</label>
                                                    <input type="text" class="form-control form-control-lg" id="name"
                                                           value="' . $name . '" required readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="email" class="form-label fw-semibold">Email</label>
                                                    <input type="email" class="form-control form-control-lg bg-light" id="email"
                                                           value="' . $email . '" disabled>
                                                    <div class="form-text">Email нельзя изменить</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="phone" class="form-label fw-semibold">Телефон</label>
                                                    <input type="tel" class="form-control form-control-lg" id="phone"
                                                           value="' . $phone . '" placeholder="+7 (999) 123-45-67" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="address" class="form-label fw-semibold">Адрес</label>
                                                    <input type="text" class="form-control form-control-lg" id="address"
                                                           value="' . $address . '" placeholder="г. Москва, ул. Примерная, д. 1" readonly>
                                                </div>
                                            </div>
                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-primary btn-lg" id="btn-edit-profile">
                                                    <i class="bi bi-pencil me-2"></i>Редактировать
                                                </button>
                                                <button type="submit" class="btn btn-success btn-lg d-none" id="btn-save-profile">
                                                    <i class="bi bi-check-circle me-2"></i>Сохранить
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-lg d-none" id="btn-cancel-edit">
                                                    <i class="bi bi-x-circle me-2"></i>Отмена
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <div id="save-message" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <!-- Смена пароля -->
                                <div class="card shadow-lg border-0 mb-4">
                                    <div class="card-header bg-warning text-dark py-3">
                                        <h5 class="mb-0"><i class="bi bi-key me-2"></i>Смена пароля</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <form action="/profile/change-password" method="POST">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label fw-semibold">Текущий пароль *</label>
                                                <input type="password" class="form-control form-control-lg" id="current_password"
                                                       name="current_password" required>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="new_password" class="form-label fw-semibold">Новый пароль *</label>
                                                    <input type="password" class="form-control form-control-lg" id="new_password"
                                                           name="new_password" required minlength="6">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="confirm_password" class="form-label fw-semibold">Подтвердите пароль *</label>
                                                    <input type="password" class="form-control form-control-lg" id="confirm_password"
                                                           name="confirm_password" required>
                                                </div>
                                            </div>
                                            <div class="mt-4 text-end">
                                                <button type="submit" class="btn btn-warning btn-lg">
                                                    <i class="bi bi-key me-2"></i>Изменить пароль
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- История заказов -->
                                <div class="card shadow-lg border-0">
                                    <div class="card-header bg-success text-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-box me-2"></i>История заказов</h5>
                                    </div>
                                    <div class="card-body p-4">';

        if (empty($orders)) {
            $content .= '<div class="text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">У вас пока нет заказов</h5>
                            <p class="text-muted mb-4">Оформите свой первый заказ в нашем магазине!</p>
                            <a href="/products" class="btn btn-primary btn-lg">
                                <i class="bi bi-cart3 me-2"></i>Перейти к покупкам
                            </a>
                        </div>';
        } else {
            $content .= '<div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr><th>№</th><th>Дата</th><th>Сумма</th><th>Товаров</th><th>Статус</th><th>Действие</th></tr>
                                </thead>
                                <tbody>';

            foreach ($orders as $index => $order) {
                $orderNum = count($orders) - $index;
                $orderDate = date('d.m.Y H:i', strtotime($order['created'] ?? $order['created_at']));
                $orderSum = number_format($order['all_sum'] ?? 0, 0, '.', ' ');
                $productsCount = count($order['products'] ?? []);

                $content .= '<tr>
                                <td><strong>#' . $orderNum . '</strong></td>
                                <td>' . $orderDate . '</td>
                                <td><strong class="text-primary">' . $orderSum . ' ₽</strong></td>
                                <td>' . $productsCount . ' шт.</td>
                                <td><span class="badge bg-success">Выполнен</span></td>
                                <td>
                                    <a href="/orders/view/' . $order['id'] . '" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>';
            }

            $content .= '</tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="/orders" class="btn btn-primary">Все заказы <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>';
        }

        $content .= '</div>
            </div>
        </div>
        
        <script>
        document.addEventListener(\'DOMContentLoaded\', function() {
            // Остальной код редактирования профиля
            const form = document.getElementById(\'profile-edit-form\');
            const btnEdit = document.getElementById(\'btn-edit-profile\');
            const btnSave = document.getElementById(\'btn-save-profile\');
            const btnCancel = document.getElementById(\'btn-cancel-edit\');
            const msgBox = document.getElementById(\'save-message\');
            
            const inputs = {
                name: document.getElementById(\'name\'),
                phone: document.getElementById(\'phone\'),
                address: document.getElementById(\'address\')
            };
            
            const originalValues = {
                name: inputs.name.value,
                phone: inputs.phone.value,
                address: inputs.address.value
            };
            
            let isEditing = false;
            
            btnEdit.addEventListener(\'click\', function() {
                isEditing = true;
                Object.values(inputs).forEach(input => input.readOnly = false);
                inputs.name.focus();
                btnEdit.classList.add(\'d-none\');
                btnSave.classList.remove(\'d-none\');
                btnCancel.classList.remove(\'d-none\');
            });
            
            btnCancel.addEventListener(\'click\', function() {
                isEditing = false;
                Object.values(inputs).forEach(input => {
                    input.readOnly = true;
                    input.value = originalValues[input.id];
                });
                btnEdit.classList.remove(\'d-none\');
                btnSave.classList.add(\'d-none\');
                btnCancel.classList.add(\'d-none\');
                msgBox.innerHTML = \'\';
            });
            
            form.addEventListener(\'submit\', async function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append(\'name\', inputs.name.value);
                formData.append(\'phone\', inputs.phone.value);
                formData.append(\'address\', inputs.address.value);
                
                try {
                    const response = await fetch("/profile-update.php", {
                        method: "POST",
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        msgBox.innerHTML = "<div class=\\"alert alert-success\\">" + result.message + "</div>";
                        Object.assign(originalValues, {
                            name: inputs.name.value,
                            phone: inputs.phone.value,
                            address: inputs.address.value
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        msgBox.innerHTML = "<div class=\\"alert alert-danger\\">" + result.message + "</div>";
                    }
                } catch (error) {
                    msgBox.innerHTML = "<div class=\\"alert alert-danger\\">Ошибка: " + error.message + "</div>";
                }
            });
        });
        </script>';

        return parent::getTemplate($content);
    }
}