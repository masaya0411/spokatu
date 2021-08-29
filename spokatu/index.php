<!DOCTYPE html>
<html lang="ja">

<!-- ヘッド -->
<?php
$siteTitle = 'トップページ';
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
            <div class="main-wrap">
                <img src="img/2020__tokyo-olympic.jpeg" alt="オリンピック画像">
                <div class="left-wrap">
                    <p>
                    <span class="logo">スポカツ！！</span>とは</br>
                    </br>
                    東京オリンピック開催にあたり開発された、自分のお気に入りのスポーツ選手を登録し、保存できるサービスです。</br>
                    今すぐお気に入りのアスリートを登録して、東京オリンピックを盛り上げましょう！
                    </p>
                    <a href="signup.php">今すぐユーザー登録する</a>
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