/**
 * @file fis config
 * @description bundle css & js
 * @author yuhui06
 * @date 2018/5/25
 */

fis.set('file-name', 'jquery.json-editor');

fis.match('**', {release: false});

fis.match('src/index.js', {
    optimizer: fis.plugin('uglify-js'),
    preprocessor: fis.plugin('js-require-css', {mode: 'inline'}),
    release: fis.get('file-name') + '.min.js'
});

fis.media('debug').match('src/index.js', {
    optimizer: null
});

