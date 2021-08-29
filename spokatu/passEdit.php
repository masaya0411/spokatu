<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ====================================================
// 画面処理
// ====================================================

// ユーザー情報を取得し、格納
$userData = getUser($_SESSION['user_id']);
debug('ユーザー情報：'.print_r($userData,true));

// POST送信されているか
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    // バリデーションチェック
    // 未入力
    validRequired($pass_old,'pass_old');
    validRequired($pass_new,'pass_new');
    validRequired($pass_new_re,'pass_new_re');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        // パスワード形式か
        validPass($pass_old,'pass_old');
        validPass($pass_new,'pass_new');

        // 古いパスワードとDBのパスワードを照合
        if(!password_verify($pass_old, $userData['pass'])){
            $err_msg['pass_old'] = MSG10;
        }

        // 新しいパスワードと古いパスワードが同じか
        if($pass_old === $pass_new){
            $err_msg['pass_new'] = MSG11;
        }

        // パスワードとパスワード再入力が同じか
        validMatch($pass_new, $pass_new_re, 'pass_new_re');

        if(empty($err_msg)){
            debug('バリデーションOK');

            try {

                $dbh = dbConnect();
                $sql = 'UPDATE users SET pass = :pass WHERE id = :id';
                $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new,PASSWORD_DEFAULT));
                // クエリ実行
                $stmt = queryPost($dbh, $sql, $data);

                // クエリ成功の場合
                if($stmt){
                    // 成功メッセージを格納
                    $_SESSION['msg_success'] = SUC01;

                    // メールを送信
                    $username = $userData['name'];
                    $from = 'info@webukatu.com';
                    $to = $userData['email'];
                    $subject = 'パスワード変更通知｜SPOKATU';
                    $comment = <<<EOT
{$username}　さん
パスワードが変更されました。
                      
////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);

                    header("Location:mypage.php");

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
$siteTitle = 'パスワード変更';
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
                    <h2 class="form-title">パスワード変更</h2>
                    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></div>
                    <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err';?>">
                        古いパスワード
                        <input type="password" name="pass_old" value="<?php if(!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['pass_old'])) echo $err_msg['pass_old'];?></div>
                    <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err';?>">
                        新しいパスワード
                        <input type="password" name="pass_new" value="<?php if(!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new'];?></div>
                    <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err';?>">
                        新しいパスワード（再入力）
                        <input type="password" name="pass_new_re" value="<?php if(!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re'];?></div>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="変更する">
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>