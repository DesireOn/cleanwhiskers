const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'production');
}

Encore
  .setOutputPath(path.resolve(__dirname, 'public/'))
  .setPublicPath('/')
  .addEntry('main', './assets/app.js')
//  .addEntry('js/mobile-nav-toggle', './assets/js/mobile-nav-toggle.js')
  .copyFiles({ from: './src/public', to: '[path][name].[ext]' })
  .copyFiles({ from: './assets/js', to: 'js/[name].[ext]' })
  
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabelPresetEnv((cfg) => { cfg.useBuiltIns = 'usage'; cfg.corejs = 3; });

module.exports = Encore.getWebpackConfig();
