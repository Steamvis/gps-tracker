const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/admin/app.js', 'public/admin/js/')
    .js('resources/js/admin/sweetalert.js', 'public/admin/js')
    .scripts([
        'resources/js/admin/table.js',
        'resources/js/admin/util.js'
    ], 'public/admin/js/app_common.js')
    .scripts(['resources/js/admin/map.js'], 'public/admin/js/map.js')
    .scripts(['resources/js/admin/upload-file.js'], 'public/admin/js/upload-file.js')
    .sass('resources/sass/landing/app.scss', 'public/landing/css')
    .sass('resources/sass/admin/sb-admin-2.scss', 'public/admin/css/')
    .sass('resources/sass/admin/font-awesome.scss', 'public/admin/css/')
    .copyDirectory('resources/images', 'public/images')
    .version();
