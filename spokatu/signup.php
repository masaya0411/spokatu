<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================================================
// 画面処理
// ====================================================
// POSTされていた場合
if(!empty($_POST)){

    // 変数に値を格納
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    // 未入力チェック
    validRequired($name, 'name');
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($pass_re, 'pass_re');

    if(empty($err_msg)){

        // 名前の最大文字数チェック
        validMaxLen($name, 'name');

        // email形式チェック
        validEmail($email,'email');
        // emailの最大文字数チェック
        validMaxLen($email,'email');
        // email重複チェック
        validEmailDup($email);

        // パスワードの半角英数字チェック
        validHalf($pass,'pass');
        // パスワードの最大文字チェック
        validMaxLen($pass,'pass');
        // パスワードの最小文字数チェック
        validMinLen($pass,'pass');

        // パスワード再入力の最大文字数チェック
        validMaxLen($pass_re,'pass_re');
        // パスワード再入力の最小文字数チェック
        validMinLen($pass_re,'pass_re');

        if(empty($err_msg)){
            // パスワードとパスワード再入力が同値かチェック
            validMatch($pass, $pass_re, 'pass_re');

            if(empty($err_msg)){
                try {
                    // DB接続
                    $dbh = dbConnect();
                    // SQL文作成
                    $sql = 'INSERT INTO users( email, pass, name, login_time, create_date) VALUES(:email, :pass, :name, :login_time, :create_date)';
                    $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':name' => $name, ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
                    // クエリ実行
                    $stmt = queryPost($dbh, $sql, $data);

                    // クエリ成功の場合
                    if($stmt){
                        // ログイン有効期限（デフォルトで1時間）
                        $sesLimit = 60*60;
                        // 最終ログイン日時を現在時間に
                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sesLimit;
                        // ユーザーIDを格納
                        $_SESSION['user_id'] = $dbh->lastInsertId();

                        debug('セッション変数の中身'.print_r($_SESSION,true));

                        header("Location:mypage.php");
                    }
                } catch(Exception $e) {
                    error_log('エラー発生：' . $e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'ユーザー登録';
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
                    <h2 class="form-title">ユーザー登録</h2>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common'];?>
                    </div>
                    <label class="<?php if(!empty($err_msg['name'])) echo 'err';?>">
                        名前
                        <input type="text" name="name" value="<?php if(!empty($_POST['name'])) echo $_POST['name']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['name'])) echo $err_msg['name'];?>
                    </div>
                    <label class="<?php if(!empty($err_msg['email'])) echo 'err';?>">
                        メールアドレス
                        <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass'])) echo 'err';?>">
                        パスワード
                        <span style="font-size: 12px;">※英数字６文字以上</span>
                        <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err';?>">
                        パスワード再入力
                        <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];?>
                    </div>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="登録">
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