const mix = require('laravel-mix');

mix.scripts(['resources/js/admin/map.js'], 'public/admin/js/map.js')
  .version();
