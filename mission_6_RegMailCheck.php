<?php 
	require 'src/Exception.php';
	require 'src/PHPMailer.php';
	require 'src/SMTP.php';
	require 'setting.php';

	session_start();
	header("Content-type: text/html");
	//クロスサイトリクエストフォージェリ（CSRF）対策
	$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
	$token = $_SESSION['token'];
	//クリックジャッキング対策
	header('X-FRAME-OPTIONS: SAMEORIGIN');
	//DB接続 start
	$dsn='host';
	$user='user';	
	$password = 'password';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	//テーブル登録
	$sql = "CREATE TABLE IF NOT EXISTS pre_member"
	."("
	."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
	."urltoken VARCHAR(128) NOT NULL,"
	."mail VARCHAR(50) NOT NULL,"
	."flag TINYINT(1) NOT NULL DEFAULT 0"
	.");";
	$stmt = $pdo->query($sql);

	//エラー時に進まないようにする 0/1:有/無
	$error_flg = 1;
	//nameのmailに入ってる値によって処理を変える
	//メールアドレスが入力されていないなら、もう一度入力させる
	if(empty($_POST["mail"])){
		echo "メールアドレスを入力してください";
		$error_flg = 0;
	}
	//メールアドレスの項目に何かしら入力有り
	else{
		$mail = $_POST["mail"];
		if($mail == ''){
			//エラーメッセージ
			echo "メールアドレスが入力されていません。";
			$error_flg = 0;
		}
		else{
			if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail)){
				echo "メールの形式が正しくありません";
				$error_flg = 0;
			}
		}
	}

	//メールに関連してエラーがでていない時->flgに1が入っている時 pre_memberにトークンとメルアドを登録
	if($error_flg == 1){
		$urltoken = hash('sha256',uniqid(rand(),1));
		$url = "https://xxx/mission_6_RegForm.php"."?urltoken=".$urltoken;
		$Pdate = date("Y/m/d H:i:s");
		$sql=$pdo->prepare("INSERT INTO pre_member (urltoken,mail,Pdate) VALUES (:urltoken,:mail,:Pdate)");
		$sql->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
		$sql->bindParam(':mail', $mail, PDO::PARAM_STR);
		$sql->bindParam(':Pdate', $Pdate, PDO::PARAM_STR);
		$sql->execute();

		//メールの設定いろいろ
		// PHPMailerのインスタンス生成
	    $mail_instance = new PHPMailer\PHPMailer\PHPMailer();

	    $mail_instance->isSMTP(); // SMTPを使うようにメーラーを設定する
	    $mail_instance->SMTPAuth = true;
	    $mail_instance->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
	    $mail_instance->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
	    $mail_instance->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
	    $mail_instance->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
	    $mail_instance->Port = SMTP_PORT; // 接続するTCPポート

	    // メール内容設定
	    	//宛先
		$mailTo = $mail;
		$mail = "From:xxx";
	    $mail_instance->CharSet = "UTF-8";
	    $mail_instance->Encoding = "base64";
	    $mail_instance->setFrom(MAIL_FROM,MAIL_FROM_NAME);
	    $mail_instance->addAddress($mailTo, $mail); //受信者（送信先）を追加する
	//    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
	//    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
	//    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
	    $mail_instance->Subject = MAIL_SUBJECT; // メールタイトル
	    $mail_instance->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します

		$body = "24時間内に下記URLから登録してください.".$url;
		$mail_instance->Body  = $body; // メール本文

		// メール送信の実行
	    if(!$mail_instance->send()) {
	    	echo 'メッセージは送られませんでした！';
	    	echo 'Mailer Error: ' . $mail->ErrorInfo;
	    } else {
	    	echo 'メールを確認してください';
	    }
	}
 ?>
 <!DOCTYPE html>
 <html>
 <head>
 	<title>メール確認画面</title>
 </head>
 <body>
 	<?php if($error_flg == 1): ?>
 	<form action="mission_6_RegForm.php">
 		<input type="text" name="mail"value="<?php if(isset($mailTo)){echo $mailTo;} ?>">
 		<p>URLが記載されたメールが届きます。</p>
 	</form>
 <?php endif; ?>
 </body>
 </html>


 		<!--
　　　　問題点：メールアドレスからなのに登録認証OKと出力される理由。どこに入ってるかわかんない。ifがおかしかった
	問題点：HTML画面を表示するときとしないときの分け方。考察するmission6regformのページ htmlにphpのif文をつけると消えるようになる。。


	 -->