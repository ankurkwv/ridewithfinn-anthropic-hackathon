let mix = require('laravel-mix');

mix.version();

// mix.copyDirectory('resources/webfonts', 'public/webfonts');

// mix.copy([
// 	'resources/js/util.js',
// 	'resources/js/breakpoints.min.js',
// 	'resources/js/browser.min.js',
// 	'resources/js/jquery.min.js',
// 	'resources/js/main.js'], 'public/js');

mix.js('resources/js/app.js', 'js');
// mix.js('resources/js/form.js', 'js');

// mix.sass('resources/sass/main.scss', 'css')
// .sourceMaps()
// .webpackConfig({devtool: 'source-map'});

// mix.sass('resources/sass/forms.scss', 'css')
// .sourceMaps()
// .webpackConfig({devtool: 'source-map'});

// mix.browserSync('wedding.test');
