const path = require('path'); // Turn absolute urls into relative ones
// const ExtractTextPlugin = require('extract-text-webpack-plugin'); // Allow to separate css from js files

let config = {
    watch: true,
    entry: {
        app: './assets/js/app.js'
    },
    output: {
        path: path.resolve('../../admin130mdhxh9/quotation-bundle'),
        filename: 'quotation-bundle.js',
        publicPath: '/../../admin130mdhxh9/quotation-bundle/'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: ['babel-loader']
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader', 'sass-loader']
            }
        ]
    },
    plugins: [
        // new ExtractTextPlugin('styles.css')
    ]
};

module.exports = config;








// const path = require('path');
//
// var quotation_bundle = {
//     mode: 'development',
//     entry: {
//         app: './assets/js/app.js'
//     },
//     watch: true,
//     output: {
//         path: path.resolve('../../admin130mdhxh9/quotation-bundle'),
//         filename: 'quotation-bundle.js'
//     },
//     module: {
//         rules: [
//             {
//                 test: /\.js$/,
//                 exclude: /(node_modules|bower_components)/,
//                 use: ['babel-loader']
//             },
//             {
//                 test: /\.css$/,
//                 use: ['style-loader', 'css-loader']
//             },
//             {
//                 test: /\.scss$/,
//                 use: ['style-loader', 'css-loader', 'sass-loader']
//             }
//         ]
//     }
// }
//
// var Encore = require('@symfony/webpack-encore');
//
// Encore.configureRuntimeEnvironment('dev');
//
// Encore
//     // directory where compiled assets will be stored
//     .setOutputPath('public/build/')
//     // public path used by the web server to access the output path
//     .setPublicPath('/build')
//     // only needed for CDN's or sub-directory deploy
//     //.setManifestKeyPrefix('build/')
//
//     /*
//      * ENTRY CONFIG
//      *
//      * Add 1 entry for each "page" of your app
//      * (including one that's included on every page - e.g. "app")
//      *
//      * Each entry will result in one JavaScript file (e.g. app.js)
//      * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
//      */
//     .addEntry('app', './assets/js/app.js')
//     //.addEntry('page1', './assets/js/page1.js')
//     //.addEntry('page2', './assets/js/page2.js')
//
//     // will require an extra script tag for runtime.js
//     // but, you probably want this, unless you're building a single-page app
//     .enableSingleRuntimeChunk()
//
//     .cleanupOutputBeforeBuild()
//     .enableSourceMaps(!Encore.isProduction())
//     // enables hashed filenames (e.g. app.abc123.css)
//     .enableVersioning(Encore.isProduction())
//
//     // uncomment if you use TypeScript
//     //.enableTypeScriptLoader()
//
//     // uncomment if you use Sass/SCSS files
//     .enableSassLoader()
//
// // uncomment if you're having problems with a jQuery plugin
// //.autoProvidejQuery()
// ;
//
// var config = Encore.getWebpackConfig();
//
// // add an extension
// config.resolve.extensions.push('json');
//
// module.exports = [quotation_bundle, config];