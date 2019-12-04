<!-- <?php
 // pre_memberの中身を見る箇所
// DB connect
	$dsn='host';
	$user='user';	
	$password = 'password';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	$sql = $pdo -> query("SELECT * FROM pre_member");
	$res = $sql -> fetchAll();
	foreach($res as $row){
		echo $row['id'].' ';
		echo $row['urltoken'].' ';
		echo $row['mail'].' ';
		echo $row['flag'].'<br>';
	}
 ?>
<?php 
// memberの中身を見る箇所
// DB connect
	$dsn='host';
	$user='user';	
	$password = 'password';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	$sql = $pdo -> query("SELECT * FROM member");
	$res = $sql -> fetchAll();
	foreach($res as $row){
		echo $row['id'].' ';
		echo $row['mail'].' ';
		echo $row['password'].'<br>';
	}
 ?> -->
<?php 
	session_start();
	 
	header("Content-type: text/html; charset=utf-8");
	 
	//クロスサイトリクエストフォージェリ（CSRF）対策
	$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
	$token = $_SESSION['token'];
	 
	//クリックジャッキング対策
	header('X-FRAME-OPTIONS: SAMEORIGIN');
	// DB connect
	$dsn='host';
	$user='user';	
	$password = 'password';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	//このフラグはエラーを通ると1を立てる。
	$error_flg = 0;
	$urltoken = $_POST["urltoken"];

	//ここでもう一度prememberに格納されているmailをtokenを参照にして取り出してくる。
	$sql = $pdo -> query("SELECT * FROM pre_member WHERE urltoken = '$urltoken'");
	$res = $sql -> fetchAll();
	foreach($res as $row){
		$mail_ID = $row['mail'];
	}
	//パスワードを本登録のテーブルに格納する
	if(isset($_POST["password_1"])){
		if($_POST["password_1"] == $_POST["password_2"]){
			$sql = $pdo->prepare("INSERT INTO member (mail, password) VALUES (:mail, :password_1)");
			$sql->bindParam(':mail', $mail, PDO::PARAM_STR);
			$mail = $mail_ID;
			$sql->bindParam(':password_1', $password_1, PDO::PARAM_STR);
			$password_1 = $_POST["password_1"];
			$sql->execute();
		}
		else{
			echo "パスワードと確認用パスワードの入力が一致しません";
			$error_flg = 1;
		}
	}
	else{
		echo "パスワードを入力してください！";
		$error_flg = 1;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>本登録画面</title>
</head>
<body>
	<?php if($error_flg == 0): ?>
	<h1>確認用</h1>
	<p>ID:<?php echo $mail; ?></p>
	<p>あなたのパスワード:<?php echo $password_1; ?></p>
	<?php endif; ?>
</body>
</html>

<!-- 
htmlの中にphp埋め込んでページ表示の良しあしを決める際
if():<-このコロンを忘れがちｗｗｗこれはもはやテンプレかも～
insert into の構文には順序がある。bindParamには第一引数に:変数名、第二$変数名、PDO第三引数
メールアドレスが出力されない問題regmailcheckまでは入ってるそれ以降が入ってない
メールアドレスはテーブルから抽出する予定
５０行目のWHEREはテーブル名＝'$変数名'私は’’を忘れていた
 -->