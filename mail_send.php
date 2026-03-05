<?php
/* Раскомментировать строки ниже для отладки, если что-то пойдёт не так */
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mail/Exception.php';
require 'mail/PHPMailer.php';
require 'mail/SMTP.php';

// Загрузка конфига
$config = require './mail_config.php';

/**
 * Функция для вывода ошибок валидации
 */
function problem($error)
{
    echo "<h3>В форме есть ошибки:</h3>";
    echo "<p style='color: red;'>" . $error . "</p>";
    echo "<b>Пожалуйста, вернитесь назад и исправьте их.</b>";
    die();
}

/**
 * Очистка строк от вредоносных вставок
 */
function clean_string($string)
{
    $bad = array("content-type", "bcc:", "to:", "cc:", "href");
    return str_replace($bad, "", $string);
}

if (isset($_POST['email'])) {
    // Проверка на существование обязательных полей
    if (
        empty($_POST['name']) ||
        empty($_POST['email']) ||
        empty($_POST['subject']) ||
        empty($_POST['message'])
    ) {
        problem('Все поля, отмеченные звездочкой, обязательны для заполнения.');
    }

    // Подготовка данных
    $email_to = $config['email_to'];
    $email_from = clean_string($_POST['email']);
    $name = clean_string($_POST['name']);
    $subject = clean_string($_POST['subject']);
    $message_text = clean_string($_POST['message']);
    
    // Формирование тела письма (HTML)
    $full_message = "<b>Имя отправителя:</b> " . $name . "<br>";
    $full_message .= "<b>Адрес отправителя:</b> " . $email_from . "<br><br>";
    $full_message .= "<b>Сообщение:</b><br>" . nl2br($message_text);

    $error_message = "";

    // 1. Валидация Email через встроенный фильтр PHP (пропустит любые домены)
    if (!filter_var($email_from, FILTER_VALIDATE_EMAIL)) {
        $error_message .= 'Введенный Email адрес некорректен.<br>';
    }

    // 2. Валидация имени (разрешаем буквы разных языков, цифры и пробелы)
    $name_exp = "/^[\p{L}\d\s.'&*-]+$/u";
    if (!preg_match($name_exp, $name)) {
        $error_message .= 'Имя содержит недопустимые символы.<br>';
    }

    // 3. Проверка длины сообщения
    if (mb_strlen($message_text) < 2) {
        $error_message .= 'Сообщение слишком короткое.<br>';
    }

    // Если есть ошибки - стоп процесс
    if (strlen($error_message) > 0) {
        problem($error_message);
    }

    $mail = new PHPMailer(true);

    try {
        // Настройки сервера из mail_config.php
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_to; // Яндекс требует, чтобы логин совпадал с почтой отправителя
        $mail->Password   = $config['mail_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        // Получатели
        $mail->setFrom($email_to, 'AlmaDyne Music Contact Form'); 
        $mail->addAddress($email_to, 'ATD');
        $mail->addReplyTo($email_from, $name);

        // Контент
        $mail->isHTML(true);
        $mail->Subject = "AlmaDyne Music: " . $subject;
        $mail->Body    = $full_message;
        $mail->AltBody = strip_tags($full_message); // Текстовая версия без HTML тегов

        $mail->send();

        // Успешный финал с таймером
        echo "<h3>Сообщение было успешно отправлено.</h3>";
        echo "<h4>Вы будете перенаправлены на главную страницу через <span id='countdown'>7</span> секунд.</h4>";
        echo "<p><b>Или нажмите на ссылку: <a href='index.html'>Вернуться на главную страницу</a></b></p>";
        echo "<script>
            const countdownDisplay = document.getElementById('countdown');
            let seconds = 7;
            const intervalId = setInterval(function() {
                seconds--;
                countdownDisplay.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(intervalId);
                    window.location.href = 'index.html';
                }
            }, 1000);
        </script>";

    } catch (Exception $e) {
        echo "<h3>Ошибка отправки!</h3>";
        echo "Описание: {$mail->ErrorInfo}";
    }
}
?>
