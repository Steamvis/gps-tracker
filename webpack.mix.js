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

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/admin/app.js', 'public/admin/js/')
    .sass('resources/sass/landing/app.scss', 'public/landing/css')
    .sass('resources/sass/admin/sb-admin-2.scss', 'public/admin/css/')
    .sass('resources/sass/admin/font-awesome.scss', 'public/admin/css/')
    .version();
