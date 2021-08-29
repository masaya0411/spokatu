<?php
//================================
// ログ
//================================
// ログを取るか
ini_set('log_errors','on');
// ログファイルの出力先
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
$debug_flg = false;
// デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//================================
// セッションの有効期限を伸ばす・準備
//================================
// セッションセッションファイルの置き場を変更する
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッションスタート
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && $_SESSION['login_limit']){
        debug('ログイン期限タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit']) );
    }
}

//================================
// 定数
//================================
define('MSG01','入力必須です');
define('MSG02','Emailの形式で入力してください');
define('MSG03','256文字以内で入力してください');
define('MSG04','そのEmailは既に登録されています');
define('MSG05','半角英数字で入力してください');
define('MSG06','6文字以上で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08','パスワード（再入力）が合っていません');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','古いパスワードが違います');
define('MSG11','古いパスワードと同じです');
define('MSG12','文字で入力してください');
define('MSG13','正しくありません');
define('MSG14','有効期限切れです');
define('SUC01','パスワードを変更しました');
define('SUC02','メールを送信しました');
define('SUC03','選手を登録しました');
define('SUC04','削除しました');

//================================
// グローバル変数
//================================
// エラー用変数
$err_msg = array();

// バリデーション関数（未入力チェック）
function validRequired($str, $key){
    if($str === ''){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
// バリデーション関数（email形式チェック）
function validEmail($str, $key){
    if(!preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
// バリデーション関数（email重複チェック）
function validEmailDup($email){
    global $err_msg;
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG04;
        }
    } catch (Exception $e) {
        $err_msg['common'] = MSG07;
        error_log('エラー発生：' . $e->getMessage());
    }
}
// バリデーション関数（半角英数字チェック）
function validHalf($str, $key){
    if(!preg_match("/^[0-9a-zA-Z]*$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}

// バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}
// バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}
// バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG08;
    }
}
// バリデーション関数（パスワードチェック）
function validPass($str, $key){
    validHalf($str, $key);
    validMaxLen($str, $key);
    validMinLen($str, $key);
}
// バリデーション関数（固定長チェック）
function validLength($str, $key, $len = 8){
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len.MSG12;
    }
}
// バリデーション関数（セレクトチェック）
function validSelect($str, $key){
    if(!preg_match("/^[1-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG13;
    }
}



// DB接続関数
function dbConnect(){
    $dsn = 'mysql:dbname=spokatu;host=localhost;charset=utf8';
    $username = 'root';
    $password = 'root';
    $options = array(
        // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    // PDOオブジェクトを生成
    $dbh = new PDO($dsn, $username, $password, $options);
    return $dbh;
}
// SQL実行関数
function queryPost($dbh, $sql, $data){
    // クエリ作成
    $stmt = $dbh->prepare($sql);
    // プレースホルダに値をセットし、実行
    if(!$stmt->execute($data)){
        debug('クエリ失敗しました。');
        debug('失敗したSQL：'.print_r($stmt, true));
        $err_msg['common'] = MSG07;
        return 0;
    }else{
        debug('クエリ成功');
        return $stmt;
    }
}

// ユーザー情報取得関数
function getUser($u_id){
    debug('ユーザー情報を取得します。');
    try {

        // DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ結果を１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

// 選手データ取得関数
function getPlayer($u_id, $p_id){
    debug('選手データを取得します。');
    debug('ユーザーID：'.$u_id);
    debug('選手ID：'.$p_id);

    try {

        $dbh = dbConnect();
        $sql = 'SELECT * FROM players WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            // クエリを１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

// 自分の全登録登録を取得する関数
function getMyplayers($u_id){
    debug('自分の登録選手を取得します。');
    debug('ユーザーIDj：'.$u_id);

    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM players WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // 全データを返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}

// 1人の選手データを返す関数
function getPlayerOne($p_id){
    debug('選手情報を取得します');
    debug('選手ID：'.$p_id);

    try{
        $dbh = dbConnect();
        $sql = 'SELECT p.id, p.name, p.comment, p.pic1, p.pic2, p.pic3, c.name AS category FROM players AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
        $data = array(':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // 選手情報を１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

// 20件の選手データを取ってくる関数
function getMyplayerList($u_id, $currentMinNum = 1, $category, $order, $span = 20){
    debug('選手情報を取得します。');

    try {
        
            $dbh = dbConnect();
            // 件数用SQL
            $sql = 'SELECT id FROM players WHERE user_id = :u_id AND delete_flg = 0';
            if(!empty($category)) $sql .= ' AND category_id = '.$category;
            if(!empty($order)){
                switch($order){
                    case 1:
                        $sql .= ' ORDER BY create_date DESC';
                        break;
                    case 2;
                        $sql .= ' ORDER BY create_date ASC';
                        break;
                }
            }
            $data = array(':u_id' => $u_id);

            $stmt = queryPost($dbh, $sql, $data);

            $rst['total'] = $stmt->rowCount(); //総レコード数
            $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
            if(!$stmt){
                return false;
            }

            // ページング用SQL
            $sql = 'SELECT * FROM players WHERE user_id = :u_id AND delete_flg = 0';
            if(!empty($category)) $sql .= ' AND category_id = '.$category;
            if(!empty($order)){
                switch($order){
                    case 1:
                        $sql .= ' ORDER BY create_date DESC';
                        break;
                    case 2;
                        $sql .= ' ORDER BY create_date ASC';
                        break;
                }
            }
            $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
            $data = array(':u_id' => $u_id);
            debug('SQL:'.$sql);

            $stmt = queryPost($dbh, $sql, $data);

            if($stmt){
                // 全レコード返却
                $rst['data'] = $stmt->fetchAll();
                return $rst;
            }else{
                return false;
            }
    } catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

// カテゴリーデータ取得関数
function getCategory(){
    debug('カテゴリーデータを取得します。');

    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category';
        $data = array();

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // 全データ返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

// メール送信関数
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        // 文字化けしないように設定
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        // メール送信
        $result = mb_send_mail($to, $subject, $comment, "From:".$from);

        if($result){
            debug('メールを送信しました。');
        }else{
            debug('【エラー発生】メール送信できませんでした。');
        }
    }
}

// sessionを1回だけ取得できる
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}

// 認証キー作成関数
function makeRandKey($length = 8){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i=0; $i < $length; $i++) { 
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}

// 画像アップロード関数
function uploadImg($file,$key){
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file,true));

    if(isset($file['error']) && is_int($file['error'])) {
        try{
        // バリデーション
        // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
        //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。

        switch ($file['error']) {
            case UPLOAD_ERR_OK: //OK
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('ファイルが選択されていません');
            case UPLOAD_ERR_INI_SIZE:
                throw new RuntimeException('ファイルサイズが大きすぎます');
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('ファイルサイズが大きすぎます');
            default:
                throw new RuntimeException('その他のエラーが発生しました。');
        }

        // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
        // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
        $type = @exif_imagetype($file['tmp_name']);
        if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new RuntimeException('画像の形式が未対応です。');
        }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if(!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました。');
      }

      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました。');
      debug('ファイルパス：'.$path);
      return $path;

        } catch (RuntimeExeption $e) {
            global $err_msg;
            $err_msg[$key] = $e->getMessage();

        }
    }
}

// サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}

// フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    // ユーザーデータがある場合
    if(!empty($dbFormData)){
        // フォームにエラーがある場合
        if(!empty($err_msg[$str])){
            // POSTにデータがある場合
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }else{
                // ない場合（基本ない）DBデータを表示
                return sanitize($dbFormData[$str]);
            }
        }else{
            // POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }
        }

    }else{
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}

// 画像表示関数
function showImg($path){
    if(empty($path)){
        return 'img/unnamed.png';
    }else{
        return $path;
    }
}

// ページネーション関数
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagenation($currentPageNum, $totalPageNum, $link = '', $pageColNum = 3){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク2個出す
  if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク1個、右に１個出す
  }elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク1個だす。
  }elseif($currentPageNum == 2 && $totalPageNum >= $pageColNum){
      $minPageNum = $currentPageNum - 1;
      $maxPageNum = $currentPageNum + 1;
  // 現ページが1の場合は左に何も出さない。右に2個出す。
  }elseif($currentPageNum == 1 && $totalPageNum >= $pageColNum){
      $minPageNum = $currentPageNum;
      $maxPageNum = $currentPageNum + 2;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
      $minPageNum = 1;
      $maxPageNum = $totalPageNum;
  }else{
      $minPageNum = $currentPageNum -1;
      $maxPageNum = $currentPageNum + 1;
  }

  echo '<div class="pagination">';
    echo'<ul class="pagination-list">';
        if($currentPageNum != 1){
            echo '<li class="list-item"><a href="?p=1">&lt;</a></li>';
        }
        for($i = $minPageNum; $i <= $maxPageNum; $i++){
            echo '<li class="list-item ';
            if($currentPageNum == $i ){ echo 'active'; }
            echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
        }
        if($currentPageNum != $maxPageNum && $maxPageNum > 1){
            echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
        }
    echo '</ul>';
  echo '</div>';
}

function appendGetParam($arr_del_key = array()){
    if(!empty($_GET)){
        $str = '?';
        foreach($_GET as $key => $val){
            if(!in_array($key,$arr_del_key,true)){
                $str .= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");
        return $str;
    }
}

// ログイン確認関数
function isLogin(){
    // ログインしている場合
if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。');
    
    // ログイン有効期限が切れているか
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time() ){
        debug('ログイン有効期限オーバーです。');

        // セッションを削除
        session_destroy();
        return false;

    }else{  //有効期限内の場合
        debug('ログイン有効期限内です。');
        return true;
        
    }

    }else{  //ログインしていない場合
        debug('未ログインです。');
        return false;
    }
}
?>