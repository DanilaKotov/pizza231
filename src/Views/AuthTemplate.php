<?php

declare(strict_types=1);

namespace App\Views;

class AuthTemplate extends BaseTemplate
{
    /**
     * Шаблон регистрации
     */
    public static function getRegisterTemplate(): string
    {
        $content = <<<HTML
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card shadow-lg border-0">
                            <div class="card-body p-5">
                                <h2 class="text-center mb-4">Регистрация</h2>
                                <p class="text-muted text-center mb-4">Создайте аккаунт для оформления заказов</p>
                                <form action="/auth/register" method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Ваше имя *</label>
                                        <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Пароль *</label>
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required minlength="6">
                                        <div class="form-text">Минимум 6 символов</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password_confirm" class="form-label">Подтвердите пароль *</label>
                                        <input type="password" class="form-control form-control-lg" id="password_confirm" name="password_confirm" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                        Зарегистрироваться
                                    </button>
                                    <p class="text-center mb-0 text-muted">
                                        Уже есть аккаунт? <a href="/login" class="fw-bold">Войти</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            HTML;

        return parent::getTemplate($content);
    }

    /**
     * Шаблон входа
     */
    public static function getLoginTemplate(): string
    {
        $content = <<<HTML
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card shadow-lg border-0">
                            <div class="card-body p-5">
                                <h2 class="text-center mb-4">Вход</h2>
                                <p class="text-muted text-center mb-4">Войдите в свой аккаунт</p>
                                <form action="/auth/login" method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Пароль *</label>
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                        Войти
                                    </button>
                                    <p class="text-center mb-0 text-muted">
                                        Нет аккаунта? <a href="/register" class="fw-bold">Зарегистрироваться</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            HTML;

        return parent::getTemplate($content);
    }
}
