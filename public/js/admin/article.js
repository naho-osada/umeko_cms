$((function(){var a=$("#trumbowyg-editor").trumbowyg("html");a.match(/<script/)&&($(".trumbowyg-viewHTML-button").trigger("mousedown"),$("#trumbowyg-editor").trumbowyg("empty"),$("#trumbowyg-editor").trumbowyg("html",a)),""!=$("#icatch-thumbnail").attr("src")&&$(".thumbnail-area").show(),$("#icatch").on("change",(function(){var a=$(this)[0].files[0];console.log(a),console.log(a.size);for(var t=0,e=!1,r=new Array;$("#image_ex"+t).length>0;)a.type==$("#image_ex"+t).val()&&(e=!0),r.push($("#image_ex"+t).val()),t++;if(!e)return $("#icatch").after('<p class="err-msg" id="icatch-err">画像を指定してください。拡張子は'+r.join(" 、 ")+"が利用できます。</p>"),$("#icatch").val(""),$("#icatch-thumbnail").attr("src",""),$(".thumbnail-area").hide(),!1;if(a.size/1024>$("#max_filesize").val())return $("#icatch").after('<p class="err-msg" id="icatch-err">ファイルサイズは'+$("#max_filesize").val()/1024+"MB以内にしてください。</p>"),!1;$("#icatch-err").remove();var i=new FileReader;i.readAsDataURL(a),i.onload=function(){$("#icatch-thumbnail").attr("src",""),$("#icatch-thumbnail").attr("src",i.result),$(".thumbnail-area").show()}})),$("#clear").on("click",(function(){$("#icatch").val(""),$("#icatch-thumbnail").attr("src",""),$(".thumbnail-area").hide(),$("#save-delete").length>0&&$("#save-delete").val(1)}))}));