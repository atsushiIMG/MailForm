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
	//テーブル登録
	$sql = "CREATE TABLE IF NOT EXISTS member"
	."("
	."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
	."mail VARCHAR(50) NOT NULL,"
	."password VARCHAR(128) NOT NULL"
	.");";
	$stmt = $pdo->query($sql);
	//このフラグはエラーを通ると1を立てる。
	$error_flg = 0;
	if(empty($_GET)){
		echo "不正なアクセスかもしれません";
		header("Location: mission_6_inputmailAD.php");
	}
	else{
		$urltoken = isset($_GET[urltoken]) ? $_GET[urltoken] : NULL;
		if($urltoken == ''){
			echo "登録からやり直してください";
			$error_flg = 1;
		}
		else{
			//仮登録テーブルに格納されているトークンと$urltokenが一致したらパスワード入力フォームを表示
			$sql = "SELECT urltoken FROM pre_member WHERE urltoken = '$urltoken' and flag = 0";
			$stmt = $pdo->query($sql);
			$res = $stmt->fetchAll();
			//res内に結果行が入っていたら、会員登録画面を表示さセル,pre_memberのフラグを立てる
			if(isset($res)){
				//UPDATE [テーブル名] SET [更新処理] {WHERE [条件式]}
				// 条件式は　テーブル名='変数'　※''これをわすれない！！
				$sql = $pdo->prepare("UPDATE pre_member SET flag=:flag WHERE urltoken='$urltoken'");
				$sql->bindParam(':flag', $flag, PDO::PARAM_INT);
				$flag = 1;
				$sql->execute();
			}
			else{
				echo "トークンが一致しません";
				$error_flg = 1;
			}
		}
	}
 ?>

 <!DOCTYPE html>
 <html>
 <head>
 	<title>会員登録画面</title>
 </head>
 <body>
 	<h1>会員登録画面</h1>
 	<?php if($error_flg == 0): ?>
 	<form action="mission_6_entry.php" method="POST">
 		<input type="password" name="password_1" placeholder="パスワード">
 		<input type="password" name="password_2" placeholder="確認用">
 		<input type="text" name="urltoken" value="<?php if(isset($_GET["urltoken"])){echo $_GET["urltoken"];} ?>">
 		<input type="submit" name="">
 	</form>
 <?php endif; ?>
 </body>
 </html>
<!-- 
新規INSERT
削除DELETE
編集UPDATE
これらは引数を用いて列の選択はすることができる
しかしセルの指定まで行きたいならWHEREでcol名指定することでセルの選択まで行ける
メルアドの引継ぎってどうすればいいの？？
今はregformで引き継げない状態
 -->