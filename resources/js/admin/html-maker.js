/**
 * HTML生成で使用するjs
 */
require('jquery/dist/jquery.js');
window.$ = window.jQuery = require('jquery');
$(function(){
  // HTML生成をクリックでダウンロードアニメーション
  $('#html-maker').on('click', function() {
    $('#loading').removeClass('loaded');
    document.cookie='htmlmaker=true';
    var downloadTimer = setInterval(function () {
      if(getCookieValue('downloadok')) {
        document.cookie = "downloadok=; max-age=0; path=/admin/html";
        // ダウンロードアニメ削除
        $('#loading').addClass('loaded');
        clearInterval(downloadTimer);
      }
      // ダウンロード中
    }, 1000);
  });
  
});

/**
 * getCookieValue
 * クッキーを取得する
 * @param key
 * @return cookiesArray or empty
 */
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
