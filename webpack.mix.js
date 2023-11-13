let mix = require('laravel-mix');
let path = require('path');
let fs = require('fs');
let glob = require('glob');
require('laravel-mix-transpile-node-modules');
require('laravel-mix-polyfill');

let appBasePath = './clientSide/packs'; // where the source files located

let jsEntries = []; // listing to compile

glob.sync(appBasePath + '/**/*.js*').forEach(function (fpath) {
    if (fs.existsSync(fpath)) {
        jsEntries.push(fpath)
    }
})

mix.webpackConfig({
    target: ['web', 'es5'],
    resolve: {
        alias: {
            '@clientSide': path.resolve(__dirname, 'clientSide'),
        },
        modules: ['node_modules'],
    },
    context: __dirname,
    node: {__filename: true}
});

mix
    .setPublicPath('frontend/web/webpack')
    .setResourceRoot('/webpack/')
    .disableNotifications()

for (const jsEntry of jsEntries) {
    let rel_path = jsEntry.replace(appBasePath, '')

    mix.js(jsEntry, rel_path)
}
/**
 * Transpile node_modules in production
 */
if (mix.inProduction()) {
}

mix.vue()
    .version()
    .sourceMaps(false)

if (mix.inProduction()) {
    mix
        .transpileNodeModules()
        .polyfill({
            enabled: true,
            useBuiltIns: "entry",
            targets: "firefox 46, IE 11"
        });
}
