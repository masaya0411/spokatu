<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


// ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
// カレントページのGETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// パラメータに不正な値が入っているかチェック
if(!is_int((int)$currentPageNum)){
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:mypage.php");
}
// 表示件数
$listSpan = 20;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
$u_id = $_SESSION['user_id'];
$categoty = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
$order = (!empty($_GET['order'])) ? $_GET['order'] : '';
$playerData = getMyplayerList($u_id, $currentMinNum, $categoty, $order);
$dbCategoryData = getCategory();
$dbFormDataName = getUser($u_id);

debug('取得した選手データ：'.print_r($playerData,true));
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
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
        <section id="section" style="overflow: hidden;">
            <section class="list">
                <h2 class="player-title"><span><?php echo sanitize($dbFormDataName['name']); ?></span>さんの　お気に入り選手</h2>
                <div class="search-title">
                    <div class="search-left">
                        全 <span class="total-num"><?php echo sanitize($playerData['total']); ?></span> 人
                    </div>
                    <div class="search-right">
                        <span class="num"><?php echo (!empty($playerData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($playerData['data']); ?></span>人 / <span class="num"><?php echo sanitize($playerData['total']); ?></span>人中
                    </div>
                </div>
                <div class="panel-list">

                    <?php
                    foreach($playerData['data'] as $key => $val):
                    ?>
                    <a href="playerDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                        <div class="panel-head">
                            <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>" style="height: 110px; width: 100%;">
                        </div>
                        <div class="panel-body">
                            <div class="panel-title">
                                <span class="category">
                                    <?php 
                                    foreach($dbCategoryData as $key => $value){
                                        if($val['category_id'] === $value['id']) echo $value['name'];
                                    }
                                    ?>
                                </span>
                                <p><?php echo sanitize($val['name']); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php
                    endforeach;
                    ?>
                </div>
                <?php pagenation($currentPageNum, $playerData['total_page']); ?>
            </section>
            <aside class="sidebar">
                <form action="" method="get">
                    <h1 class="title">競技</h1>
                    <select name="c_id" id="">
                        <option value="0" <?php if(getFormData('c_id',true) == 0){ echo 'selected'; } ?>>選択してください</option>
                        <?php foreach($dbCategoryData as $key => $val): ?>
                        <option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id',true) == $val['id']){ echo 'selected'; } ?>>
                            <?php echo $val['name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <h1 class="title">表示順</h1>
                    <select name="order" id="">
                        <option value="0" <?php if(getFormData('order',true) == 0){ echo 'selected'; } ?>>選択してください</option>
                        <option value="1" <?php if(getFormData('order',true) == 1){ echo 'selected'; } ?>>新着順</option>
                        <option value="2" <?php if(getFormData('order',true) == 2){ echo 'selected'; } ?>>投稿順</option>
                    </select>
                    <input type="submit" value="検索">
                </form>
                <div class="link-wrap">
                    <a href="registPlayer.php">選手を登録する</a>
                    <a href="passEdit.php">パスワード変更</a>
                    <a href="withdraw.php">退会</a>
                </div>
            </aside>
        </section>
    </main>

    <!-- フッター -->
    <?php
    require('footer.php');
    ?>

</body>
</html>