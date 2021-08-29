<?php

// =================================================
// ログイン認証
// =================================================
// ログインしている場合
if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。');
    
    // ログイン有効期限が切れているか
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time() ){
        debug('ログイン有効期限オーバーです。');

        // セッションを削除
        session_destroy();
        // ログインページへ遷移
        header("Location:login.php");

    }else{  //有効期限内の場合
        debug('ログイン有効期限内です。');

        // ログイン日時を現在日時に更新
        $_SESSION['login_date'] = time();


        if(basename($_SERVER['PHP_SELF']) === 'login.php'){
            debug('マイページへ遷移します。');
            header("Location:mypage.php");
        }

    }

    }else{  //ログインしていない場合
        debug('未ログインユーザーです。');
        if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
            header("Location:login.php");
        }
    }