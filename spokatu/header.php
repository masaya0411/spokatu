<!-- ヘッダー -->
<header id="header">
        <div class="side-width">
            <h1 class="site-title"><a href="index.php">スポカツ！！</a></h1>
            <nav id="top-nav">
                <ul>
                    <?php if(empty($_SESSION['user_id'])){ ?>

                    <li><a href="signup.php">ユーザー登録</a></li>
                    <li><a href="login.php">ログイン</a></li>
                    
                    <?php }else{ ?>

                    <li><a href="mypage.php">マイページ</a></li>
                    <li><a href="logout.php">ログアウト</a></li>

                    <?php } ?>
                </ul>
            </nav>
        </div>
    </header>