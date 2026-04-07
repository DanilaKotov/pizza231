<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Views\OrderTemplate;
// 👇 Импорты PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 👇 Подключения шаблонов и сервисов
require_once __DIR__ . '/../Views/BaseTemplate.php';
require_once __DIR__ . '/../Views/OrderTemplate.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/IStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DatabaseStorage.php';

use App\Configs\Config;

class OrderController
{
    public function get(): string
    {
        // Обработка POST-запроса (форма доставки)
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST') {
            return $this->create();
        }

        // GET-запрос: отображение корзины
        // 👇 ИЗМЕНЕНО: создание модели с внедрением зависимости
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            $serviceStorage = new \App\Services\DatabaseStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }

        $data = $productModel->getBasketData();

        return OrderTemplate::getOrderTemplate($data);
    }

    /**
     * Создание заказа
     */
    private function create(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 👇 ИЗМЕНЕНО: создание модели для чтения товаров из корзины
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $model = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            $serviceStorage = new \App\Services\DatabaseStorage();
            $model = new Product($serviceStorage, Config::FILE_PRODUCTS);
        }

        // Получаем товары из корзины
        $products = $model->getBasketData();

        // Используем метод prepareData() для подготовки заказа
        $orderData = $model->prepareData($_POST, $products);

        // Если пользователь авторизован - добавляем его ID
        if (isset($_SESSION['user'])) {
            $orderData['user_id'] = $_SESSION['user']['id'];
        }

        // 👇 ИЗМЕНЕНО: создание модели для СОХРАНЕНИЯ заказа (другой ресурс!)
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $orderService = new \App\Services\FileStorage();
            $orderModel = new Product($orderService, Config::FILE_ORDERS);
        } else {
            $orderService = new \App\Services\DatabaseStorage();
            $orderModel = new Product($orderService, Config::FILE_ORDERS);
        }

        // Сохранение заказа через сервис
        $orderModel->saveData($orderData);

        // 👇 ОТПРАВКА EMAIL с деталями заказа
        $this->sendMail($orderData['email'] ?? '', $orderData);

        // Очистка корзины
        $_SESSION['basket'] = [];

        // Флеш-сообщение
        $_SESSION['flash'] = "Спасибо! Ваш заказ успешно создан и передан службе доставки";
        $_SESSION['flash_type'] = "success";

        // Редирект на главную
        header("Location: /");
        exit();
    }

    /**
     * Отправка подтверждающего email с деталями заказа
     * @param string $email Адрес получателя
     * @param array $orderData Данные заказа
     * @return bool Успешность отправки
     */
    public function sendMail($email, $orderData = [])
    {
        $mail = new PHPMailer(true);

        if (isset($email) && !empty($email)) {
            try {
                // Настройки
                $mail->SMTPDebug = 0;
                $mail->CharSet = 'UTF-8';

                // От кого
                $mail->SetFrom("2006danil04@gmail.com", "Компьютерный магазин: <Пиксель>");

                // Кому
                $mail->addAddress($email);

                // Формат письма
                $mail->isHTML(true);

                // SMTP настройки для Mail.ru
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;

                $mail->Port       = 465;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

                // 👇 Формируем тело письма с деталями заказа
                $fio = htmlspecialchars($orderData['fio'] ?? 'Не указано');
                $address = htmlspecialchars($orderData['address'] ?? 'Не указано');
                $phone = htmlspecialchars($orderData['phone'] ?? 'Не указано');
                $allSum = number_format($orderData['all_sum'] ?? 0, 0, '.', ' ');
                $products = $orderData['products'] ?? [];
                $orderDate = $orderData['created_at'] ?? date('d-m-Y H:i:s');

                // 👇 Список товаров в виде таблицы
                $productsTable = '<table border="0" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">';
                $productsTable .= '<thead><tr style="background: #f8f9fa;"><th style="text-align: left; border-bottom: 2px solid #dee2e6;">Товар</th><th style="text-align: center; border-bottom: 2px solid #dee2e6;">Кол-во</th><th style="text-align: right; border-bottom: 2px solid #dee2e6;">Цена</th><th style="text-align: right; border-bottom: 2px solid #dee2e6;">Сумма</th></tr></thead><tbody>';

                foreach ($products as $product) {
                    $name = htmlspecialchars($product['name'] ?? 'Товар');
                    $qty = (int) ($product['quantity'] ?? 1);
                    $price = number_format($product['price'] ?? 0, 0, '.', ' ');
                    $sum = number_format($product['sum'] ?? 0, 0, '.', ' ');

                    $productsTable .= "<tr>
                        <td style='border-bottom: 1px solid #eee;'>{$name}</td>
                        <td style='text-align: center; border-bottom: 1px solid #eee;'>{$qty} шт.</td>
                        <td style='text-align: right; border-bottom: 1px solid #eee;'>{$price} ₽</td>
                        <td style='text-align: right; border-bottom: 1px solid #eee;'><strong>{$sum} ₽</strong></td>
                    </tr>";
                }

                $productsTable .= '</tbody></table>';

                // 👇 Полное тело письма
                $mail->Subject = '📦 Ваш заказ в "Пиксель" от ' . date('d.m.Y');
                $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <style>
                            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                            .header { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 20px; border-radius: 10px 10px 0 0; }
                            .content { background: #fff; padding: 25px; border: 1px solid #dee2e6; border-radius: 0 0 10px 10px; }
                            .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
                            .label { font-weight: bold; color: #555; }
                            .total { font-size: 1.3em; color: #667eea; font-weight: bold; text-align: right; margin-top: 15px; }
                            .footer { text-align: center; color: #888; font-size: 0.9em; margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; }
                        </style>
                    </head>
                    <body>
                        <div class='header'>
                            <h2 style='margin: 0;'>🎉 Спасибо за заказ!</h2>
                            <p style='margin: 5px 0 0 0; opacity: 0.9;'>Пиксель — быстро, надёжно</p>
                        </div>
                        <div class='content'>
                            <p>Здравствуйте, <strong>{$fio}</strong>!</p>
                            <p>Ваш заказ успешно создан и передан в обработку.</p>

                            <div class='section'>
                                <h4 style='margin-top: 0;'>📋 Данные доставки</h4>
                                <p><span class='label'>👤 Получатель:</span> {$fio}</p>
                                <p><span class='label'>📞 Телефон:</span> {$phone}</p>
                                <p><span class='label'>📍 Адрес:</span> {$address}</p>
                                <p><span class='label'>📅 Дата заказа:</span> {$orderDate}</p>
                            </div>

                            <div class='section'>
                                <h4 style='margin-top: 0;'>🛒 Состав заказа</h4>
                                {$productsTable}
                                <div class='total'>💰 Итого: {$allSum} ₽</div>
                            </div>

                            <p>🚚 Менеджер свяжется с вами в ближайшее время для подтверждения доставки.</p>

                            <div class='footer'>
                                <p>Сообщение сгенерировано автоматически.<br>
                                Не отвечайте на это письмо.<br><br>
                                © 2026 Пиксель. Все права защищены.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                // 👇 Текстовая версия для почтовых клиентов без HTML
                $mail->AltBody = "Спасибо за заказ, {$fio}!\n\n"
                    . "Данные доставки:\n"
                    . "👤 {$fio}\n📞 {$phone}\n📍 {$address}\n📅 {$orderDate}\n\n"
                    . "Состав заказа:\n";

                foreach ($products as $product) {
                    $mail->AltBody .= "- {$product['name']} × {$product['quantity']} = {$product['sum']} ₽\n";
                }

                $mail->AltBody .= "\n💰 Итого: {$allSum} ₽\n\n"
                    . "Менеджер свяжется с вами в ближайшее время.\n"
                    . "© 2026 PIZZA-231";

                // Отправка
                $mail->send();
                return true;

            } catch (Exception $error) {
                $message = $error->getMessage();
                error_log("Email error: $message");

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['flash'] = "Предупреждение: Не удалось отправить письмо";
                $_SESSION['flash_type'] = "warning";

                return false;
            }
        }
        return false;
    }
}
