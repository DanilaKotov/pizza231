<?php
namespace App\Views;

require_once __DIR__ . '/BaseTemplate.php';

class RegisterTemplate extends BaseTemplate
{
    public static function getRegisterTemplate(array $errors = [], array $old = []): string
    {
        $username = htmlspecialchars($old['username'] ?? '');
        $email = htmlspecialchars($old['email'] ?? '');
        
        $errorHtml = '';
        foreach ($errors as $field => $message) {
            $errorHtml .= "<div class='alert alert-danger'>{$message}</div>";
        }

        $content = "
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card shadow'>
                        <div class='card-header bg-primary text-white'>
                            <h4 class='mb-0'>🔐 Регистрация</h4>
                        </div>
                        <div class='card-body'>
                            {$errorHtml}
                            <form method='POST' action='/register'>
                                <div class='mb-3'>
                                    <label class='form-label'>Имя пользователя *</label>
                                    <input type='text' name='username' class='form-control' value='{$username}' required>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Email *</label>
                                    <input type='email' name='email' class='form-control' value='{$email}' required>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Пароль *</label>
                                    <input type='password' name='password' class='form-control' required minlength='6'>
                                    <small class='text-muted'>Минимум 6 символов</small>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Подтвердите пароль *</label>
                                    <input type='password' name='confirm_password' class='form-control' required>
                                </div>
                                <button type='submit' class='btn btn-primary w-100'>Зарегистрироваться</button>
                            </form>
                            <p class='mt-3 text-center'>
                                Уже есть аккаунт? <a href='/login'>Войти</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return parent::getTemplate($content);
    }

    public static function getSuccessTemplate(string $message): string
    {
        $content = "
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card shadow border-success'>
                        <div class='card-header bg-success text-white'>
                            <h4 class='mb-0'>✅ Успешно!</h4>
                        </div>
                        <div class='card-body text-center'>
                            <p class='lead'>{$message}</p>
                            <a href='/login' class='btn btn-primary'>Войти в аккаунт</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        return parent::getTemplate($content);
    }

    public static function getErrorTemplate(string $message): string
    {
        $content = "
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card shadow border-danger'>
                        <div class='card-header bg-danger text-white'>
                            <h4 class='mb-0'>❌ Ошибка</h4>
                        </div>
                        <div class='card-body text-center'>
                            <p class='lead'>{$message}</p>
                            <a href='/register' class='btn btn-secondary'>Попробовать снова</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        return parent::getTemplate($content);
    }
}