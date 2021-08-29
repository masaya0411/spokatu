<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード認証ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// セッションに認証キーがあるか
if(empty($_SESSION['auth_key'])){
    header("Location:passRemindSend.php");
}
// ====================================================
// 画面処理
// ====================================================
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POSTの中身：'.print_r($_POST,true));

    // 変数に認証キーを代入
    $auth_key = $_POST['token'];

    // 未入力チェック
    validRequired($auth_key,'token');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        // 固定長チェック
        validLength($auth_key,'token');
        // 半角英数字チェック
        validHalf($auth_key,'token');

        if(empty($err_msg)){
            debug('バリデーションチェックOK');

            // 認証キーがあっているか
            if($auth_key !== $_SESSION['auth_key']){
                $err_msg['common'] = MSG13;
            }

            // 認証キーが有効期限内か
            if($_SESSION['auth_key_limit'] < time()){
                $err_msg['common'] = MSG14;
            }

            if(empty($err_msg)){
                debug('認証OK');

                // パスワードを生成
                $pass = makeRandKey();

                try {
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET pass = :pass WHERE email = :email AND delete_flg = 0';
                    $data = (array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass,PASSWORD_DEFAULT)));

                    $stmt = queryPost($dbh, $sql, $data);

                    if($stmt){
                        debug('クエリ成功');

                        // メール送信
                        $from = 'msya.0411@gmail.com';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】｜SPOKATU';
                        $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/webservice_practice07/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
                        sendMail($from, $to, $subject, $comment);

                        // セッション削除
                        session_unset();
                        $_SESSION['msg_success'] = SUC02;
                        debug('セッションの中身：'.print_r($_SESSION,true));

                        header("Location:login.php");
                        exit;

                    }else{
                        debug('クエリ失敗しました。');
                        $err_msg['common'] = MSG07;
                    }
                } catch (Exception $e) {
                    error_log('エラー発生：' . $e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
?>
<?php
$siteTitle = 'パスワード再発行認証';
require('head.php');
?>

<body>
    
    <!-- ヘッダー -->
    <?php
    require('header.php');
    ?>

    <!-- メッセージ -->
    <div id="js-show-msg" class="msg-slide" style="display: none;">
        <p>
            <?php echo getSessionFlash('msg_success'); ?>
        </p>
    </div>

    <!-- メイン -->
    <main id="main" class="side-width">
        <section id="section">
            <div class="form-container">
                <form action="" method="post">
                    <h2 class="form-title" style="font-size: 16px;font-weight: normal;">ご指定のメールアドレスお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</h2>
                    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></div>
                    <label class="<?php if(!empty($err_msg['token'])) echo 'err';?>">
                        認証キー
                        <input type="text" name="token" value="<?php if(!empty($_POST['token'])) echo $_POST['token']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['token'])) echo $err_msg['token'];?></div>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="再発行する" style="padding-right: 5px;padding-left: 5px;">
                    </div>
                </form>
            </div>
            <a href="passRemindSend.php">< パスワード再発行メールを再度送信する</a>
        </section>
    </main>

    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>