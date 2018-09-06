/**
 * External dependencies
 */
const webpack = require( 'webpack' );

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	resolve: {
		modules: [
			`${ __dirname }/assets/js`,
			'node_modules',
		],
	},
	entry: {
		'edd-admin': './assets/js/admin',
		'edd-admin-tax-rates': './assets/js/admin/tax-rates',
		'edd-admin-email-tags': './assets/js/admin/email-tags',
	},
	output: {
		filename: 'assets/js/[name].js',
		path: __dirname,
	},
	module: {
		rules: [
			{
				test: /.js$/,
				use: 'babel-loader',
				exclude: /node_modules/,
				include: /assets\/js/,
			},
		],
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
	},
	plugins: [
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery',
		} ),
	],
};

if ( config.mode !== 'production' ) {
	config.devtool = process.env.SOURCEMAP || 'source-map';
}

module.exports = config;
