const path = require('path');

module.exports = {
    mode: 'development',
    entry: {
        app: './assets/js/app.js'
    },
    watch:true,
    output: {
        path: path.resolve('../../adminToua/quotation-bundle'),
        filename: 'quotation-bundle.js'
    }
}

