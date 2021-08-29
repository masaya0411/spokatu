<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 選手IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品情報を取得
$viewData = getPlayerOne($p_id);
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:mypage.php");
}
debug('取得したDBデータ：'.print_r($viewData,true));
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>
<?php
$siteTitle = '選手詳細';
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
            <div class="player-title">
                <span class="category"><?php echo sanitize($viewData['category']); ?></span>
                <?php echo sanitize($viewData['name']); ?>
            </div>
            <div class="player-img-container">
                <div class="img-main">
                    <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="<?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
                </div>
                <div class="img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                </div>
            </div>
                <div class="player-detail">
                    <p>
                        <?php echo sanitize($viewData['comment']); ?>
                    </p>
                </div>
                <div class="player-item">
                    <div class="item-right">
                        <a href="registPlayer.php?p_id=<?php echo sanitize($viewData['id']); ?>"><i class="fas fa-edit fa-2x"></i></a>
                        <a href="mypage.php" style="color: #85858a">
                            <i class="fas fa-trash-alt fa-2x js-click-delete" data-playerid="<?php echo sanitize($viewData['id']); ?>"></i>
                        </a>
                    </div>
                    <div class="item-left">
                        <a href="mypage.php<?php echo appendGetParam(array('p_id')); ?>" style="text-decoration: none; font-size: 18px;">&lt; 一覧へ戻る</a>
                    </div>
                </div>
        </section>
    </main>

    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>