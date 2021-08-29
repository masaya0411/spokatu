<?php 

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ====================================================
// 画面処理
// ====================================================
// POST送信されていた場合
if(!empty($_POST)){

    // 変数定義
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false;

    // バリデーションチェック
    // email形式チェック
    validEmail($email,'email');
    // email最大文字数チェック
    validMaxLen($email,'email');

    // パスワードチェック
    validPass($pass,'pass');

    // 未入力チェック
    validRequired($email,'email');
    validRequired($pass,'pass');

    if(empty($err_msg)){

        try {

            // DBへ接続
            $dbh = dbConnect();
            $sql = 'SELECT pass, id FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            // クエリ結果を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            debug('クエリ結果の中身：'.print_r($result,true));
            
            // パスワード照合
            if(!empty($result) && password_verify($pass, array_shift($result))){
                debug('パスワードがマッチしました。');
                
                // ログイン有効期限（1時間）
                $sesLimit = 60*60;
                // セッションに最終ログイン日時を保存
                $_SESSION['login_date'] = time();
                
                // セッションにログイン有効期限を保存
                // ログイン保持にチェックがあるか
                if($pass_save){
                    debug('ログイン保持にチェックがあります。');
                    $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                }else{
                    debug('ログイン保持にチェックがありません。');
                    $_SESSION['login_limit'] = $sesLimit;
                }
                
                // セッションにユーザーIDを保存
                $_SESSION['user_id'] = $result['id'];
                
                debug('セッション変数の中身：'.print_r($_SESSION,true));
                debug('マイページへ遷移します。');
                header("Location:mypage.php");
            }else{
                debug('パスワードがアンマッチです。');
                $err_msg['common'] = MSG09;
            }
        } catch(Exception $e) {
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'ログイン';
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
                    <h2 class="form-title">ログイン</h2>
                    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></div>
                    <label class="<?php if(!empty($err_msg['email'])) echo 'err';?>">
                        メールアドレス
                        <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email'];?></div>
                    <label class="<?php if(!empty($err_msg['pass'])) echo 'err';?>">
                        パスワード
                        <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?></div>
                    <label>
                        <input type="checkbox" name="pass_save">
                        次回からログインを省略する
                    </label>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="ログイン">
                    </div>
                    パスワードを忘れた方は
                    <a href="passRemindSend.php">コチラ</a>
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