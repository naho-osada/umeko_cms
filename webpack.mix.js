const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .js('resources/js/admin/trumbowyg.js', 'public/js/admin')
    .js('resources/js/admin/article.js', 'public/js/admin') // Article
    .js('resources/js/admin/html-maker.js', 'public/js/admin') // Html Maker
    .sass('resources/sass/style.scss', 'public/css') // 公開用CSS
    .sass('resources/sass/admin/style.scss', 'public/css/admin') // 管理用CSS
    .sass('resources/sass/admin/trumbowyg.scss', 'public/css/admin'); // 管理用CSS
