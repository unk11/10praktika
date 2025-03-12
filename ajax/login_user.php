<?php
session_start();
include("../settings/connect_datebase.php");
include("../recaptcha/autoload.php");

header('Content-Type: application/json');

$secret = "6LfGnfIqAAAAALvOB-QhDYsNBAUcdQGN0gpibNcV";

if (!isset($_POST['g-recaptcha-response'])) {
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не была отправлена."]);
    exit;
}

// Проверяем reCAPTCHA
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

if (!$resp->isSuccess()) {
    // Выводим подробности ошибки для отладки
    $error_codes = $resp->getErrorCodes();
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не пройдена.", "error_codes" => $error_codes]);
    exit;
}

// Получаем данные из формы
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

// Ищем пользователя в БД
$query_user = $mysqli->prepare("SELECT * FROM `users` WHERE `login` = ? AND `password` = ?");
$query_user->bind_param("ss", $login, $password);
$query_user->execute();
$result = $query_user->get_result();

$id = -1;
$role = -1;

while ($user_read = $result->fetch_row()) {
    $id = $user_read[0];
    $role = $user_read[3]; // Поле роли (0 - пользователь, 1 - админ)
}

// Проверяем, найден ли пользователь
if ($id == -1) {
    echo json_encode(["status" => "error", "message" => "Ошибка: Неверный логин или пароль."]);
    exit;
}

// Авторизуем пользователя
$_SESSION['user'] = $id;

// Определяем, куда перенаправлять
$redirect_url = ($role == 1) ? "admin.php" : "user.php";

// Возвращаем успешный ответ с URL для редиректа
echo json_encode(["status" => "success", "redirect" => $redirect_url]);
?>