<?php
session_start();
header("Content-type: text/html; charset=utf-8");
//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');
// echo now();
?>
 
<!DOCTYPE html>
<html>
<head>
	<title>	input mail AD</title>
</head>
<body>
	<h1>メール登録画面dayo</h1>
	<form action="mission_6_RegMailCheck.php" method="POST">	
		<p>メールアドレス：<input type="text" name="mail"　placeholder="input mail"></p>
		<input type="hidden" name="token" value="<?=$token?>">
		<input type="submit" name="" value="登録">
	</form>
</body>
</html>