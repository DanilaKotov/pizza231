<?php
namespace App\Controllers;

// 👇 Автозагрузчик Composer (обязательно для PHPMailer!)
require_once __DIR__ . '/../../vendor/autoload.php';

// Подключения
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Views/OrderTemplate.php';
require_once __DIR__ . '/../Configs/Config.php';
require_once __DIR__ . '/../Services/ILoadStorage.php';
require_once __DIR__ . '/../Services/ISaveStorage.php';
require_once __DIR__ . '/../Services/FileStorage.php';
require_once __DIR__ . '/../Services/DBStorage.php';
require_once __DIR__ . '/../Services/ProductDBStorage.php';
require_once __DIR__ . '/../Services/OrderDBStorage.php';
require_once __DIR__ . '/../Services/ValidateOrderData.php';

// 👇 Импорты для PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use App\Models\Product;
use App\Models\Order;
use App\Views\OrderTemplate;
use App\Configs\Config;
use App\Services\ValidateOrderData;

class OrderController 
{
    public function get(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST') {
            return $this->create();
        }

        // 👇 Запускаем сессию
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ✅ ИСПРАВЛЕНО: создание модели с поддержкой БД
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $productModel = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            // ✅ ProductDBStorage + TABLE_PRODUCTS для БД
            $serviceStorage = new \App\Services\ProductDBStorage();
            $productModel = new Product($serviceStorage, Config::TABLE_PRODUCTS);
        }

        $data = $productModel->getBasketData();

        // Получаем ошибки и старые данные из сессии (если есть)
        $errors = $_SESSION['order_errors'] ?? [];
        $oldData = $_SESSION['order_data'] ?? [];
        
        // Очищаем сессию после чтения
        unset($_SESSION['order_errors'], $_SESSION['order_data']);

        return OrderTemplate::getOrderTemplate($data, $errors, $oldData);
    }

    private function create(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Валидация данных формы заказа
        $orderValidationErrors = ValidateOrderData::validate($_POST);
        if (!empty($orderValidationErrors)) {
            $_SESSION['flash'] = 'Пожалуйста, исправьте ошибки в форме заказа';
            $_SESSION['flash_type'] = 'error';
            $_SESSION['order_errors'] = $orderValidationErrors;
            $_SESSION['order_data'] = $_POST;
            header("Location: /order");
            exit();
        }

        // ✅ Чтение товаров для корзины (исправлено)
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new \App\Services\FileStorage();
            $model = new Product($serviceStorage, Config::FILE_PRODUCTS);
        } else {
            // ✅ ProductDBStorage + TABLE_PRODUCTS
            $serviceStorage = new \App\Services\ProductDBStorage();
            $model = new Product($serviceStorage, Config::TABLE_PRODUCTS);
        }

        $products = $model->getBasketData();
        
        // Если корзина пуста, показываем страницу заказа с ошибкой
        if (empty($products)) {
            $_SESSION['flash'] = 'Корзина пуста';
            $_SESSION['flash_type'] = 'warning';
            header("Location: /order");
            exit();
        }
        $orderData = $model->prepareData($_POST, $products);

        if (isset($_SESSION['user'])) {
            $orderData['user_id'] = $_SESSION['user']['id'];
        }

        // ✅ СОХРАНЕНИЕ ЗАКАЗА (исправлено)
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $orderService = new \App\Services\FileStorage();
            $orderModel = new Order($orderService, Config::FILE_ORDERS);
        } else {
            // ✅ OrderDBStorage + TABLE_ORDERS для БД
            $orderService = new \App\Services\OrderDBStorage();
            $orderModel = new Order($orderService, Config::TABLE_ORDERS);
        }

        $orderModel->saveData($orderData);

        $this->sendMail($orderData['email'] ?? '', $orderData);
        $_SESSION['basket'] = [];
        $_SESSION['flash'] = "Спасибо! Ваш заказ успешно создан";
        $_SESSION['flash_type'] = "success";

        header("Location: /");
        exit();
    }

    public function sendMail($email, $orderData = [])
    {
        $mail = new PHPMailer(true);

        if (isset($email) && !empty($email)) {
            try {
                $mail->SMTPDebug = 0;
                $mail->CharSet = 'UTF-8';
                $mail->SetFrom("2006danil04@gmail.com", "Компьютерный магазин: <Пиксель>");
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '2006danil04@gmail.com';
                $mail->Password = 'qnfn wlod dyyl ltfr';
                $mail->Port = 465;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

                $fio = htmlspecialchars($orderData['fio'] ?? 'Не указано');
                $address = htmlspecialchars($orderData['address'] ?? 'Не указано');
                $phone = htmlspecialchars($orderData['phone'] ?? 'Не указано');
                $allSum = number_format($orderData['all_sum'] ?? 0, 0, '.', ' ');
                $products = $orderData['products'] ?? [];
                $orderDate = $orderData['created_at'] ?? date('d-m-Y H:i:s');

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

                $mail->Subject = '📦 Ваш заказ в "Пиксель" от ' . date('d.m.Y');
                $mail->Body = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;color:#333;line-height:1.6}.header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px;border-radius:10px 10px 0 0}.content{background:#fff;padding:25px;border:1px solid #dee2e6;border-radius:0 0 10px 10px}.section{margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px}.label{font-weight:bold;color:#555}.total{font-size:1.3em;color:#667eea;font-weight:bold;text-align:right;margin-top:15px}.footer{text-align:center;color:#888;font-size:0.9em;margin-top:25px;padding-top:15px;border-top:1px solid #eee}</style></head><body><div class='header'><h2 style='margin:0'>🎉 Спасибо за заказ!</h2><p style='margin:5px 0 0 0;opacity:0.9'>Пиксель — быстро, надёжно</p></div><div class='content'><p>Здравствуйте, <strong>{$fio}</strong>!</p><p>Ваш заказ успешно создан и передан в обработку.</p><div class='section'><h4 style='margin-top:0'>📋 Данные доставки</h4><p><span class='label'>👤 Получатель:</span> {$fio}</p><p><span class='label'>📞 Телефон:</span> {$phone}</p><p><span class='label'>📍 Адрес:</span> {$address}</p><p><span class='label'>📅 Дата заказа:</span> {$orderDate}</p></div><div class='section'><h4 style='margin-top:0'>🛒 Состав заказа</h4>{$productsTable}<div class='total'>💰 Итого: {$allSum} ₽</div></div><p>🚚 Менеджер свяжется с вами в ближайшее время для подтверждения доставки.</p><div class='footer'><p>Сообщение сгенерировано автоматически.<br>Не отвечайте на это письмо.<br><br>© 2026 Пиксель. Все права защищены.</p></div></div></body></html>";

                $mail->AltBody = "Спасибо за заказ, {$fio}!\n\nДанные доставки:\n👤 {$fio}\n📞 {$phone}\n📍 {$address}\n📅 {$orderDate}\n\nСостав заказа:\n";
                foreach ($products as $product) {
                    $mail->AltBody .= "- {$product['name']} × {$product['quantity']} = {$product['sum']} ₽\n";
                }
                $mail->AltBody .= "\n💰 Итого: {$allSum} ₽\n\nМенеджер свяжется с вами в ближайшее время.\n© 2026 PIZZA-231";

                $mail->send();
                return true;

            } catch (Exception $error) {
                $message = $error->getMessage();
                error_log("Email error: $message");
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                $_SESSION['flash'] = "Предупреждение: Не удалось отправить письмо";
                $_SESSION['flash_type'] = "warning";
                return false;
            }
        }
        return false;
    }
}