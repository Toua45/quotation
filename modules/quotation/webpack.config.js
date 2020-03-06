const path = require('path');

module.exports = {
    mode: 'development',
    entry: {
        app: './assets/js/app.js'
    },
    watch:true,
    output: {
        path: path.resolve('../../adminLionel/quotation-bundle'),
        filename: 'quotation-bundle.js'
    }
};
