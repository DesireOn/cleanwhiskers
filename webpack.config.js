const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'production');
}

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('main', './assets/app.js') // <- make sure this exists
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabelPresetEnv(cfg => { cfg.useBuiltIns = 'usage'; cfg.corejs = 3; });

module.exports = Encore.getWebpackConfig();
