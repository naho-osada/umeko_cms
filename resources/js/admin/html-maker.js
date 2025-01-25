/**
 * HTML生成で使用するjs
 */
require('jquery/dist/jquery.js');
window.$ = window.jQuery = require('jquery');
$(function(){
  // クリックしたらクッキーをセット
  // intervalでそのクッキーがいるか監視する
  $('#html-maker').on('click', function(){
    document.cookie='htmlmaker=true';
    var downloadTimer = setInterval(function () {
      if(getCookieValue('downloadok')) {
        document.cookie = "downloadok=; max-age=0; path=/admin/html";
        // ダウンロードアニメ削除
        clearInterval(downloadTimer);
      }
      // ダウンロード中
    }, 1000);
  });
  
});
function getCookieValue(key) {
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
      var cookiesArray = cookie.split('='); 
      if (cookiesArray[0].trim() == key.trim()) { 
          return cookiesArray[1];  // (key[0],value[1])
      }
  }
  return '';
}
// クッキーがある間はダウンロード中
// なくなったら削除する
// $(function(){
//   console.log("html-maker")
//   $('#html-maker').on('click', function(){
//     console.log("click maker")
//     console.log($('#domain').val())
//     $.ajax({
//       url: $(this).attr('formaction'),
//       type: 'post',
//       cache: false,
//       data: { 'domain':$('#domain').val() },
//       headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // これが必要
//       }
//     })
//     .done(function(data) {
//       console.log("成功")
//     }).fail(function(error) {
//       alert(error.responseJSON.message);
//     });
//   });
// });