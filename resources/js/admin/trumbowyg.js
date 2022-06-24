/**
 * trumbowyg Editor
 */
require('jquery/dist/jquery.js');
window.$ = window.jQuery = require('jquery');
require('trumbowyg/dist/trumbowyg.min.js');
require('trumbowyg/dist/langs/ja.min.js');
require('trumbowyg/dist/plugins/fontsize/trumbowyg.fontsize.min.js');
require('trumbowyg/dist/plugins/upload/trumbowyg.upload.min.js');
// 記事投稿に合わせたパスに変更
$.trumbowyg.svgPath = '../../images/trumbowyg/icons.svg';
$('#trumbowyg-editor').trumbowyg({
    lang: 'ja',
    tagsToKeep: ['script[src]', 'div'],
    btnsDef: {
        align: {
            dropdown: ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ico: 'justifyLeft'
        }
    },
    btns: [
        ['viewHTML'],
        ['undo', 'redo'],
        ['formatting'],
        ['strong', 'em', 'del', 'underline'],
        ['foreColor', 'backColor'],
        ['link'],
        // ['insertImage', 'upload', 'base64', 'noembed', 'giphy'],
        ['upload'],
        ['align'],
        ['preformatted'],
        ['horizontalRule'],
        ['fullscreen']
    ],
    plugins: {
        upload: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            serverPath: '/admin/article/upload-image',
        }
    }
});
