<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Отправка письма с подтверждением регистрации
     */
    public static function sendMailUserConfirmation(string $email, string $username, string $token): bool
    {
        // Формируем ссылку подтверждения
        $siteUrl = 'http://' . $_SERVER['HTTP_HOST'];
        $verificationLink = $siteUrl . '/verify?token=' . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 0;
            $mail->CharSet = 'UTF-8';
            $mail->SetFrom('noreply@pixel-shop.ru', 'Компьютерный магазин "Пиксель"');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);

            // SMTP настройки
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '2006danil04@gmail.com';
            $mail->Password = 'qnfn wlod dyyl ltfr';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->Subject = '🔐 Подтверждение регистрации в "Пиксель"';
            
            // 👇 ИСПРАВЛЕНО: Правильное формирование HTML-тела письма
            $mail->Body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 20px; text-align: center; }
        .content { padding: 25px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: #fff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>🎉 Добро пожаловать в Пиксель!</h2>
    </div>
    <div class="content">
        <p>Здравствуйте, <strong>' . htmlspecialchars($username) . '</strong>!</p>
        <p>Спасибо за регистрацию. Пожалуйста, подтвердите ваш email, нажав на кнопку ниже:</p>
        <p style="text-align: center;">
            <a href="' . htmlspecialchars($verificationLink) . '" class="btn">✅ Подтвердить email</a>
        </p>
        <p>Или скопируйте ссылку в браузер:<br><small>' . htmlspecialchars($verificationLink) . '</small></p>
        <p>Если вы не регистрировались — просто проигнорируйте это письмо.</p>
    </div>
    <div class="footer">
        <p>© 2026 Компьютерный магазин "Пиксель". Все права защищены.</p>
        <p>Это письмо сгенерировано автоматически, не отвечайте на него.</p>
    </div>
</body>
</html>';

            // Текстовая версия
            $mail->AltBody = "Здравствуйте, {$username}!\n\n"
                . "Подтвердите ваш email, перейдя по ссылке:\n"
                . "{$verificationLink}\n\n"
                . "Если вы не регистрировались — просто проигнорируйте это письмо.";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer error: " . $mail->ErrorInfo);
            return false;
        }
    }
}