<?php
session_start();
include("./settings/connect_datebase.php");

// Если пользователь уже авторизован, перенаправляем его
if (isset($_SESSION['user']) && $_SESSION['user'] != -1) {
    $stmt = $mysqli->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user']);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    if ($role === 0) {
        header("Location: user.php");
        exit();
    } elseif ($role === 1) {
        header("Location: admin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head> 
    <meta charset="utf-8">
    <title>Авторизация</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-menu">
        <a href="#"><img src="img/logo1.png"/></a>
        <div class="name">
            <a href="index.php">
                <div class="subname">БЕЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
                Пермский авиационный техникум им. А. Д. Швецова
            </a>
        </div>
    </div>
    <div class="space"></div>
    <div class="main">
        <div class="content">
            <div class="login">
                <div class="name">Авторизация</div>

                <div class="sub-name">Логин:</div>
                <input name="_login" type="text" placeholder="Введите логин" onkeypress="return PressToEnter(event)"/>
                
                <div class="sub-name">Пароль:</div>
                <input name="_password" type="password" placeholder="Введите пароль" onkeypress="return PressToEnter(event)"/>

                <center>
                    <div class="g-recaptcha" data-sitekey="6LfGnfIqAAAAAJxZWJKJRq-QI_cP2zH-wUHxp43h"></div>
                </center>

                <a href="regin.php">Регистрация</a>
                <br><a href="recovery.php">Забыли пароль?</a>

                <input type="button" class="button" value="Войти" onclick="LogIn()"/>
                <img src="img/loading.gif" class="loading" style="display: none;"/>
            </div>

            <div class="footer">
                © КГАПОУ "Авиатехникум", 2020
                <a href="#">Конфиденциальность</a>
                <a href="#">Условия</a>
            </div>
        </div>
    </div>

    <script>
        function LogIn() {
            var loading = document.querySelector(".loading");
            var button = document.querySelector(".button");

            var _login = document.querySelector("[name='_login']").value.trim();
            var _password = document.querySelector("[name='_password']").value.trim();
            var captcha = grecaptcha.getResponse();

            if (!_login || !_password) {
                alert("Пожалуйста, заполните все поля!");
                return;
            }
            if (!captcha.length) {
                alert("Вы не прошли проверку reCAPTCHA!");
                return;
            }

            loading.style.display = "block";
            button.disabled = true;

            var data = new FormData();
            data.append("login", _login);
            data.append("password", _password);
            data.append("g-recaptcha-response", captcha);

            $.ajax({
                url: 'ajax/login_user.php',
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = response.redirect;
                    } else {
                        alert(response.message);
                        grecaptcha.reset();
                        loading.style.display = "none";
                        button.disabled = false;
                    }
                },
                error: function () {
                    alert("Ошибка связи с сервером!");
                    loading.style.display = "none";
                    button.disabled = false;
                }
            });
        }

        function PressToEnter(e) {
            if (e.keyCode === 13) {
                LogIn();
            }
        }
    </script>
</body>
</html>