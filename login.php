<?php
	session_start();
	include("./settings/connect_datebase.php");
	
	if (isset($_SESSION['user'])) {
		if($_SESSION['user'] != -1) {
			
			$user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']);
			while($user_read = $user_query->fetch_row()) {
				if($user_read[3] == 0) header("Location: user.php");
				else if($user_read[3] == 1) header("Location: admin.php");
			}
		}
 	}
?>
<html>
	<head> 
		<meta charset="utf-8">
		<title> Авторизация </title>
		
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
				<div class = "login">
					<div class="name">Авторизация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>

					<center><div class="g-recaptcha" data-sitekey="6LfGnfIqAAAAAJxZWJKJRq-QI_cP2zH-wUHxp43h"></div></center>
					
					<a href="regin.php">Регистрация</a>
					<br><a href="recovery.php">Забыли пароль?</a>
					<input type="button" class="button" value="Войти" onclick="LogIn()"/>
					<img src = "img/loading.gif" class="loading"/>
				</div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
			function LogIn() {
				var loading = document.getElementsByClassName("loading")[0];
				var button = document.getElementsByClassName("button")[0];

				var _login = document.getElementsByName("_login")[0].value;
				var _password = document.getElementsByName("_password")[0].value;

				var captcha = grecaptcha.getResponse();
				if (!captcha.length) {
					alert("Вы не прошли проверку reCAPTCHA!");
					return;
				}

				loading.style.display = "block";
				button.className = "button_diactive";

				var data = new FormData();
				data.append("g-recaptcha-response", captcha);
				data.append("login", _login);
				data.append("password", _password);

				$.ajax({
					url: 'ajax/login_user.php',
					type: 'POST',
					data: data,
					cache: false,
					dataType: 'json',  // Ожидаем JSON-ответ
					processData: false,
					contentType: false,
					success: function (response) {
						console.log("Ответ сервера: ", response);

						if (response.status === "success") {
							window.location.href = response.redirect; // Перенаправляем на user.php или admin.php
						} else {
							alert(response.message || "Ошибка авторизации!");
							grecaptcha.reset(); // Сбросить капчу после неудачной попытки
							loading.style.display = "none";
							button.className = "button";
						}
					},
					error: function (xhr, status, error) {
						console.log('Ошибка AJAX: ', status, error);
						alert("Ошибка связи с сервером!");
						loading.style.display = "none";
						button.className = "button";
					}
				});
			}
			
			function PressToEnter(e) {
				if (e.keyCode == 13) {
					var _login = document.getElementsByName("_login")[0].value;
					var _password = document.getElementsByName("_password")[0].value;
					
					if(_password != "") {
						if(_login != "") {
							LogIn();
						}
					}
				}
			}
			
		</script>
	</body>
</html>