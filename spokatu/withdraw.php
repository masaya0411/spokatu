<?php
// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================================================
// 画面処理
// ====================================================
// POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');

    try {
        $dbh = dbConnect();
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
        $sql2 = 'UPDATE players SET delete_flg = 1 WHERE id = :u_id';
        $data = array(':u_id' => $_SESSION['user_id']);

        $stmt1 = queryPost($dbh, $sql1, $data);
        $stmt2 = queryPost($dbh, $sql2, $data);

        // クエリ成功の場合
        if($stmt1){
            // セッション削除
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug(('トップページへ遷移します。'));
            header("Location:index.php");
        } else {
            debug('クエリ失敗しました。');
            $err_msg['common'] = MSG07;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

?>
<?php
$siteTitle = '退会';
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
            <div class="area-msg">
                <div class="form-container">
                    <form action="" method="post" style="text-align: center;">
                        <h2 class="form-title">退会</h2>
                        <div class="area-msg"></div>
                        <div class="btn-container" style="float: none;">
                            <input class="btn btn-mid" type="submit" value="退会する" name="submit">
                        </div>
                    </form>
                </div>
            <a href="mypage.php">< マイページへ戻る</a>
            </div>
        </section>
    </main>
    
    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>