<footer id="footer">
        Copyright 
        <a href="index.php">スポカツ！！</a>
        . All Rights Reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script>
        $(function(){

            //フッターを最下部に固定
            var $ftr = $('#footer');
            if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
                $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
            }

            // 画像ライブプレビュー
            var $dropArea =$('.area-drop');
            var $fileInput = $('.input-file');

            $dropArea.on('dragover', function(e){
                e.stopPropagation();
                e.preventDefault();
                $(this).css('border', '3px #ccc dashed');
            });
            $dropArea.on('dragleave', function(e){
                e.stopPropagation();
                e.preventDefault();
                $(this).css('border', 'none');
            });
            $fileInput.on('change', function(e){
                $dropArea.css('border', 'none');
                var file = this.files[0],           //2.files配列にファイルが入っている
                    $img = $(this).siblings('.prev-img'),   //3.jQueryのsiblingsメソッドで兄弟のimgを取得
                    fileReader = new FileReader();  //4.ファイルを読み込むFileReaderオブジェクト

                //5.読み込むが完了した際のイベントハンドラ。imgのsrcにデータをセット
                fileReader.onload = function(event) {
                    //読み込んだデータをimgに設定
                    $img.attr('src', event.target.result).show();
                };

                //6.画像読み込み
                fileReader.readAsDataURL(file);
            });

            // 文字カウンター
            var $countUp = $('.js-counter');
            var $countView = $('.js-show-count');

            $countUp.on('keyup', function(e){
                $countView.html($(this).val().length);
            });

            // 画像切り替え
            var $switchImgSubs = $('.js-switch-img-sub');
            var $switchImgMain = $('#js-switch-img-main');

            $switchImgSubs.on('click', function(e){
                $switchImgMain.attr('src',$(this).attr('src'));
            });

            // サクセスメッセージ表示
            var $showMsg = $('#js-show-msg');
            var msg = $showMsg.text();
            if(msg.replace(/^[\s　]+|[\s　]+$/g,'').length){
                $showMsg.slideToggle(('slow'));
                setTimeout(function(){ $showMsg.slideToggle('slow'); }, 5000);
            }

            // 削除機能（Ajax）
            var $delete,
                deletePlayerId;
            $delete = $('.js-click-delete') || null;
            deletePlayerId = $delete.data('playerid') || null;
            // 数値の0はfalseと判定されてしまう。product_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
            if(deletePlayerId !== undefined && deletePlayerId !== null){
                $delete.on('click',function(){
                    var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "ajaxDelete.php",
                        data: { playerId : deletePlayerId }
                    }).done(function( data ){
                        console.log('Ajax Success');
                    }).fail(function( msg ){
                        console.log('Ajax Error');
                    });
                });
            }

        });
    </script>