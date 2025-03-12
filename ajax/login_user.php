<?php
session_start();
include("../settings/connect_datebase.php");
include("../recaptcha/autoload.php");

header('Content-Type: application/json');

$secret = "6LfGnfIqAAAAALvOB-QhDYsNBAUcdQGN0gpibNcV";

if (!isset($_POST['g-recaptcha-response'])) {
    error_log("reCAPTCHA response is missing!");
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не была отправлена."]);
    exit;
}

$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $ip);

if (!$resp->isSuccess()) {
    $errors = $resp->getErrorCodes();
    error_log("reCAPTCHA errors: " . print_r($errors, true));
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не пройдена."]);
    exit;
}

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='" . $mysqli->real_escape_string($login) . "' AND `password`='" . $mysqli->real_escape_string($password) . "';");

$id = -1;
$role = -1;

while ($user_read = $query_user->fetch_row()) {
    $id = $user_read[0];
    $role = $user_read[3];
}

if ($id == -1) {
    echo json_encode(["status" => "error", "message" => "Ошибка: Неверный логин или пароль."]);
    exit;
}

$_SESSION['user'] = $id;
$redirect_url = ($role == 1) ? "admin.php" : "user.php";

echo json_encode(["status" => "success", "redirect" => $redirect_url]);
?>