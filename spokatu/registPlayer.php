<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　選手登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ====================================================
// 画面処理
// ====================================================
// GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから選手データを取得
$dbFormData = (!empty($p_id)) ? getPlayer($_SESSION['user_id'], $p_id) : '';
// 新規登録か編集かの判断用フラグ
$edit_flg = (!empty($dbFormData)) ? true : false;
// DBからカテゴリーを取得
$dbCategoryData = getCategory();
debug('選手ID：'.$p_id);
debug('フォーム用情報：'.print_r($dbFormData,true));
debug('カテゴリー情報：'.print_r($dbCategoryData,true));

// GETパラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if(!empty($p_id) && empty($dbFormData)){
    debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
    header("Location:mypage.php");
}

if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));


    $name = $_POST['name'];
    $category = $_POST['category_id'];
    $comment = $_POST['comment'];
    // 画像をアップロードし、パスを格納
    $pic1 = ( !empty($_FILES['pic1']['name']) ) ? uploadImg($_FILES['pic1'],'pic1') : '';
    // 画像をPOSTしていないが、既にDBへ登録されていた場合は、DBのパスを入れる
    $pic1 = ( empty($pic1) && !empty($dbFormData['pic1']) ) ? $dbFormData['pic1'] : $pic1;
    $pic2 = ( !empty($_FILES['pic2']['name']) ) ? uploadImg($_FILES['pic2'],'pic1') : '';
    $pic2 = ( empty($pic2) && !empty($dbFormData['pic2']) ) ? $dbFormData['pic2'] : $pic2;
    $pic3 = ( !empty($_FILES['pic3']['name']) ) ? uploadImg($_FILES['pic3'],'pic1') : '';
    $pic3 = ( empty($pic3) && !empty($dbFormData['pic3']) ) ? $dbFormData['pic3'] : $pic3;

    // DBの情報と異なる場合にバリデーション
    if(empty($dbFormData)){
        // 名前
        validRequired($name,'name');
        validMaxLen($name,'name');
        // セレクトボックス
        validSelect($category,'category_id');
        // コメント
        validMaxLen($comment,'comment');
    }else{
        if($dbFormData['name'] !== $name){
            validRequired($name,'name');
            validMaxLen($name,'name');  
        }
        if($dbFormData['category_id'] !== $category){
            validSelect($category,'category_id');
        }
        if($dbFormData['comment'] !== $comment){
            validMaxLen($comment,'comment');
        }
    }

    if(empty($err_msg)){
        debug('バリデーションOK');

        try{
            $dbh = dbConnect();
            if($edit_flg){
                debug('DB更新です');
                $sql = 'UPDATE players SET name = :name, category_id = :category_id, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :id';
                $data = array(':name' => $name, ':category_id' => $category, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':id' => $p_id);
            }else{
                debug('DB新規登録です');
                $sql = 'INSERT INTO players ( name, category_id, comment, pic1, pic2, pic3, user_id, create_date ) VALUES ( :name, :category_id, :comment, :pic1, :pic2, :pic3, :u_id, :create_date)';
                $data = array(':name' => $name, ':category_id' => $category, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));
            }
            debug('SQL:'.$sql);
            debug('流し込みデータ：'.print_r($data,true));

            $stmt = queryPost($dbh, $sql, $data);

            if($stmt){
                $_SESSION['msg_success'] = SUC03;
                debug('マイページへ遷移します');
                header("Location:mypage.php");
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
$siteTitle = ($edit_flg) ? '選手編集' : '選手登録';
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
            <div class="form-container" style="width: 80%;">
                <form action="" method="post" enctype="multipart/form-data" class="form">
                    <h2 class="form-title"><?php echo ($edit_flg) ? '選手を編集する' : '選手を登録する'; ?></h2>
                    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></div>
                    <label class="<?php if(!empty($err_msg['name'])) echo 'err';?>">
                        選手名
                        <span class="require">必須</span>
                        <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['name'])) echo $err_msg['name'];?></div>
                    <label class="<?php if(!empty($err_msg['category_id'])) echo 'err';?>">
                        競技
                        <span class="require">必須</span>
                        <select name="category_id" id="">
                            <option value="0" <?php if( getFormData('category_id') === 0 ){ echo 'selected'; } ?>>選択してください</option>
                            <?php 
                            foreach($dbCategoryData as $key => $val){
                            ?>
                            <option value="<?php echo $val['id']?>" <?php if(getFormData('category_id') === $val['id']){ echo 'selected'; } ?>>
                                <?php echo $val['name']; ?>
                            </option>
                            <?php
                            }
                            ?>
                        </select>
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['category_id'])) echo $err_msg['category_id'];?></div>
                    <label style="overflow: hidden;" class="<?php if(!empty($err_msg['comment'])) echo 'err';?>">
                        詳細
                        <textarea class="js-counter" name="comment" cols="30" rows="10" style="height: 300px;"><?php echo getFormData('comment'); ?></textarea>
                        <div class="counter">
                            <span class="js-show-count">0</span>/300文字
                        </div>
                    </label>
                    <div class="area-msg"><?php if(!empty($err_msg['comment'])) echo $err_msg['comment'];?></div>
                    <div class="img-wrap">
                        <div class="img-container">
                            画像１
                            <label class="area-drop <?php if(!empty($err_msg['pic1'])) echo 'err';?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic1" class="input-file">
                                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>width: 100%;height: 100%;">
                                ドラッグ＆ドロップ
                            </label>
                            <div class="area-msg"><?php if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];?></div>
                        </div>
                        <div class="img-container">
                            画像２
                            <label class="area-drop <?php if(!empty($err_msg['pic2'])) echo 'err';?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic2" class="input-file">
                                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>width: 100%;height: 100%;">
                                ドラッグ＆ドロップ
                            </label>
                            <div class="area-msg"><?php if(!empty($err_msg['pic2'])) echo $err_msg['pic2'];?></div>
                        </div>
                        <div class="img-container">
                            画像３
                            <label class="area-drop <?php if(!empty($err_msg['pic3'])) echo 'err';?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic3" class="input-file">
                                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>width: 100%;height: 100%;">
                                ドラッグ＆ドロップ
                            </label>
                            <div class="area-msg"><?php if(!empty($err_msg['pic3'])) echo $err_msg['pic3'];?></div>
                        </div>
                    </div>
                    <div class="btn-container">
                        <input class="btn" type="submit" value="<?php echo ($edit_flg) ? '編集する' : '登録する'; ?>">
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