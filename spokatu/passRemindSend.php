<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード認証送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================================================
// 画面処理
// ====================================================
if(!empty($_POST)){
    debug('POST送信があります');

    $email = $_POST['email'];
    // バリデーションチェック
    validRequired($email,'email');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        validEmail($email,'email');
        validMaxLen($email,'email');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            try{
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);

                $stmt = queryPost($dbh, $sql, $data);
                // クエリ結果を取得
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if($stmt && !empty(array_shift($result))){
                    debug('クエリ成功。DB登録あり');

                    $_SESSION['msg_success'] = SUC02;

                    // 認証キーを作成
                    $auth_key = makeRandKey();

                    // メール送信
                    $from = 'msya.0411@gmail.com';
                    $to = $email;
                    $subject = '【パスワード再発行認証】｜SPOKATU';
                    $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/webservice_practice07/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/webservice_practice07/passRemindSend.php

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);

                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time()+(60*30);
                    debug('セッション変数の中身：'.print_r($_SESSION,true));

                    header("Location:passRemindRecieve.php");
                    
                }else{
                    debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                    $err_msg['common'] = MSG07;
                }
            } catch (Exception $e) {
                error_log('エラー発生：' . $e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}

?>
<?php
$siteTitle = 'パスワード再発行メール送信';
require('head.php');
?>

<body>
    
    <!-- ヘッダー -->
    <?php
    require('header.php');
    ?>

    <!-- メイン -->
    <main id="main" class="side-width">
        <section id="section">
            <div class="form-container">
                <form action="" method="post">
                    <h2 class="form-title" style="font-size: 16px;font-weight: normal;">ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</h2>
                    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></div>
                    <label class="<?php if(!empty($err_msg['email'])) echo 'err';?>">
                        メールアドレス
                        <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email'];?></div>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="送信する">
                    </div>
                </form>
            </div>
            <a href="mypage.php">< マイページへ戻る</a>
        </section>
    </main>

    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>