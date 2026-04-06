<?php
namespace App\Views;

class BaseTemplate
{
    public static function getTemplate(string $content): string
    {
        // Начинаем HTML
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ru" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Компьютерный магазин "Пиксель"</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Ваши стили -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Иконки -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Анимация флеш-сообщений -->
    <style>
        .alert[role="alert"] {
            animation: fadeInDown 0.3s ease;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container"> 
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-pc-display me-2"></i>Пиксель
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">Каталог</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/order">
                            <i class="bi bi-cart3"></i> Корзина
HTML;

        // 👇 Счётчик товаров в корзине
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $basketCount = 0;
        if (isset($_SESSION['basket']) && is_array($_SESSION['basket'])) {
            foreach ($_SESSION['basket'] as $item) {
                $basketCount += (int)($item['quantity'] ?? 1);
            }
        }
        if ($basketCount > 0) {
            $html .= <<<HTML
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem; min-width: 18px; height: 18px; padding: 2px 5px; line-height: 1;">
                                {$basketCount}
                            </span>
HTML;
        }
        
        $html .= <<<HTML
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">О нас</a>
                    </li>
HTML;

        // 🔐 Проверка авторизации (ВНЕ heredoc!)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user'])) {
            // Пользователь авторизован
            $avatar = $_SESSION['user']['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['name'] ?? 'U') . '&background=667eea&color=fff&size=32';
            $userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Пользователь');
            
            $html .= <<<HTML
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="{$avatar}" alt="Avatar" 
                                 class="rounded-circle me-2" 
                                 style="width: 32px; height: 32px; object-fit: cover;">
                            <span>{$userName}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="/profile">
                                    <i class="bi bi-person me-2"></i>Мой профиль
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/auth/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Выйти
                                </a>
                            </li>
                        </ul>
                    </li>
HTML;
        } else {
            // 👤 Пользователь не авторизован - ТОЛЬКО ОДНА КНОПКА "ВХОД"
            $html .= <<<HTML
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-3 px-4 rounded-pill" href="/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Вход
                        </a>
                    </li>
HTML;
        }

        // Закрываем навигацию и продолжаем
        $html .= <<<HTML
                </ul>
            </div>
        </div>
    </nav>
HTML;

        // 👇 Флеш-сообщения с авто-скрытием
        if (isset($_SESSION['flash'])) {
            $type = $_SESSION['flash_type'] ?? 'info';
            $message = htmlspecialchars($_SESSION['flash']);
            $alertId = 'flash-' . uniqid();
            
            $html .= <<<END
            <div class="container mt-3">
                <div id="{$alertId}" class="alert alert-{$type} alert-dismissible fade show" role="alert">
                    {$message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <script>
                setTimeout(function() {
                    var alert = document.getElementById('{$alertId}');
                    if (alert) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                            bsAlert.close();
                        } else {
                            alert.style.transition = 'opacity 0.5s ease';
                            alert.style.opacity = '0';
                            setTimeout(function() { alert.remove(); }, 500);
                        }
                    }
                }, 4000);
            </script>
END;
            
            unset($_SESSION['flash'], $_SESSION['flash_type']);
        }

        // Основной контент и футер
        $html .= <<<HTML
    <!-- Основной контент -->
    <main class="flex-grow-1 py-4">
        $content
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2026 Кемеровский кооперативный техникум. Все права защищены.</p>
            <small>Разработано студентом группы ИС-231</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;

        return $html;
    }
}