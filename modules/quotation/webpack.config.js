const path = require('path');

module.exports = {
    mode: 'development',
    entry: {
        app: './assets/js/app.js'
    },
    output: {
        path: path.resolve('../../admin130mdhxh9/quotation-bundle'),
        filename: 'quotation-bundle.js'
    }
};