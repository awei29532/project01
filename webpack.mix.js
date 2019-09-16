let mix = require('laravel-mix');

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
Mix.manifest.refresh = _ => void 0; // 不產生 mix-manifest.json
mix.babelConfig({
   plugins: ['syntax-dynamic-import']
});

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');

// mix.extract(['vue' , 'axios' ]);

mix.webpackConfig({
   output: {
      chunkFilename: 'js/[name].js'
   }
});
