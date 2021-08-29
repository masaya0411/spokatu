<?php

// 共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================================================
// Ajax処理
// ====================================================

// POST送信があり、ユーザーIDがあり、ログインしている時
if(isset($_POST['playerId']) && isset($_SESSION['user_id']) && isLogin()){
    debug('POST送信があります。');
    $p_id = $_POST['playerId'];
    debug('選手ID：'.$p_id);

    try{
        $dbh = dbConnect();
        $sql = 'UPDATE players SET delete_flg = 1 WHERE id = :p_id AND user_id = :u_id';
        $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            debug('選手を削除しました。');
            $_SESSION['msg_success'] = SUC04;

        }else{
            error_log('エラー発生：削除できませんでした。');
        }
    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}
?>