const path = require('path')

let visualizations = {
    // OUTPUT_FILE IN JSDIST: ENTRY_FILE FROM ROOT
    'visualizations/normal_distribution.js': './js/visualizations/normal_distribution.js',
    'visualizations/traffic_light.js': './js/visualizations/traffic_light.js',
    'visualizations/radar_chart.js': './js/visualizations/radar_chart.js'
}

let admin_scripts = {
    'admin/admin_log.js': './js/admin/admin_log.js',
    'admin/admin_test.js': './js/admin/admin_test.js',
    'admin/admin_tests.js': './js/admin/admin_tests.js',
    'admin/admin_evaluation.js': './js/admin/admin_evaluation.js',
    'admin/admin_evaluations.js': './js/admin/admin_evaluations.js',
    'admin/admin_import_export.js': './js/admin/admin_import_export.js'
}

let frontend_scripts = {
    'frontend/update_gscore.js': './js/frontend/update_gscore.js'
}

module.exports = {
    context: __dirname,
    entry: Object.assign(visualizations, admin_scripts, frontend_scripts),
    output: {
      path: path.resolve(__dirname, 'jsdist'),
      filename: function(module) {
        return module.chunk.name
      }
    },
    resolve: {
      alias: {
        vuejs: path.resolve(__dirname, 'node_modules', 'vue', 'dist', 'vue.esm.js'),
        'vue-wp-list-table': path.resolve(__dirname, 'node_modules', 'vue-wp-list-table', 'dist', 'vue-wp-list-table.browser.js', )
      }
    },
    
  }
  