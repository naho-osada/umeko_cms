/**
 * 記事投稿で使用するjs
 * trumbowygでjQueryを使用しているので、こちらもjQueryの書き方で実装する
 */
$(function(){
    var contents = $('#trumbowyg-editor').trumbowyg('html')
    // scriptタグが含まれるとき、デフォルトでソースモードにする（バグを防ぐため）
    if(contents.match(/<script/)) {
      $('.trumbowyg-viewHTML-button').trigger('mousedown');
      $('#trumbowyg-editor').trumbowyg('empty');
      $('#trumbowyg-editor').trumbowyg('html', contents);
    }

    if($('#icatch-thumbnail').attr('src') != '') {
      $('.thumbnail-area').show();
    }
    // アイキャッチ画像のサムネイル表示
    $('#icatch').on('change', function() {
    var file = $(this)[0].files[0];
    console.log(file);
    console.log(file['size'])
    // 指定拡張子以外は登録不可
    var i = 0;
    var exFlg = false;
    var exAry = new Array();
    while($('#image_ex' + i).length > 0) {
      if(file['type'] == $('#image_ex' + i).val()) {
        exFlg = true;
      }
      exAry.push($('#image_ex' + i).val())
      i++;
    }
    // 拡張子チェック
    if(!exFlg) {
      $('#icatch').after('<p class="err-msg" id="icatch-err">画像を指定してください。拡張子は' + exAry.join(' 、 ') + 'が利用できます。</p>');
      $('#icatch').val('');
      $('#icatch-thumbnail').attr('src', '');
      $('.thumbnail-area').hide();
      return false;
    }
    // 容量チェック
    if(file['size']/1024 > $('#max_filesize').val()) {
      $('#icatch').after('<p class="err-msg" id="icatch-err">ファイルサイズは' + $('#max_filesize').val()/1024 + 'MB以内にしてください。</p>');
      return false;
    }
    $('#icatch-err').remove();
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function() {
      $('#icatch-thumbnail').attr('src', '');
      $('#icatch-thumbnail').attr('src', reader.result);
      $('.thumbnail-area').show();
    }
    });
    $('#clear').on('click', function(){
      $('#icatch').val('');
      $('#icatch-thumbnail').attr('src', '');
      $('.thumbnail-area').hide();
      if($('#save-delete').length > 0) {
        $('#save-delete').val(1);
      }
    });
});