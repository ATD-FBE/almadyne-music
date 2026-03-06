<?php
/* Раскомментируй для отладки, если будет белый экран */
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

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
 * Функция очистки строк
 */
function clean_string($string)
{
    $bad = array("content-type", "bcc:", "to:", "cc:", "href");
    return str_replace($bad, "", $string);
}

if (isset($_POST['email'])) {
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message'])) {
        problem('Все поля, отмеченные звездочкой, обязательны для заполнения.');
    }

    // Определение переменных для полей формы
    $name = clean_string($_POST['name']);
    $email_from = clean_string($_POST['email']);
    $subject = clean_string($_POST['subject']);
    $message_text = clean_string($_POST['message']);
    
    // Проверка значений полей формы
    $error_message = "";

    if (!filter_var($email_from, FILTER_VALIDATE_EMAIL)) {
        $error_message .= 'Введенный Email адрес некорректен.<br>';
    }

    $name_exp = "/^[\p{L}\d\s.'&*-]+$/u";
    if (!preg_match($name_exp, $name)) {
        $error_message .= 'Имя содержит недопустимые символы.<br>';
    }

    if (strlen($subject) < 2) {
        $error_message .= 'Тема сообщения слишком короткая.<br>';
    }

    if (strlen($message_text) < 2) {
        $error_message .= 'Сообщение слишком короткое.<br>';
    }

    if (strlen($error_message) > 0) {
        problem($error_message);
    }

    // Отправка сообщения через Formspree
    $formspree_url = 'https://formspree.io/f/mreygpzl';

    $ch = curl_init($formspree_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Игнорирование проверки сертификата
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Игнорирование проверки хоста
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'Name' => $name,
        'Email' => $email_from,
        'Тема' => $subject,
        'Message' => $message_text
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Обработка ответа
    if ($status == 200) {
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
    } else {
        echo "Ошибка отправки! Код: $status. Ответ сервиса: $response";
    }
}
?>
