const mix = require('laravel-mix');

mix.disableNotifications();

mix.js('js/app.js', 'public/assets/js')
   .sass('sass/app.scss', 'public/assets/css');
