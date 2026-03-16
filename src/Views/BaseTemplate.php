<?php
namespace App\Views;

class BaseTemplate
{
    public static function getTemplate()
    {
        $template = <<<HTML
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
            <title>%s</title>
        </head>
        <body class="d-flex flex-column min-vh-100">
            <header>
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand d-flex align-items-center" href="/">
                            <img src="../../assets/images/logo.jpg" alt="Пиксель" width="70" height="55" class="me-3" onerror="this.style.display='none'">
                            Компьютерный магазин
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                                aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="/">Главная</a>
                                </li>
                                <li class="nav-item">
                                   <a class="nav-link active" aria-current="page" href="/catalog">Каталог</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="/about">О нас</a>
                                </li>     
                            </ul>
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="/cart">
                                        Корзина <span class="badge bg-danger" id="cart-count">0</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>

            <main class="flex-grow-1">
                %s
            </main>

            <footer class="bg-dark text-white text-center py-3">
                <div class="container">
                    <p class="mb-0">&copy; 2026 Кемеровский кооперативный техникум. Все права защищены.</p>
                    <small>Разработано студентом группы ИС-231</small>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        HTML;
        return $template;
    }
}