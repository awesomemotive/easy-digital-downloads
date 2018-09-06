/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const UglifyJS = require( 'uglify-es' );

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
		'edd-admin-backwards-compatibility': './assets/js/admin/backwards-compatibility.js',
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
		// Copy vendor files to ensure 3rd party plugins relying on a script
		// handle to exist continue to be enqueued.
		new CopyWebpackPlugin( [
			{
				from: './node_modules/chosen-js/chosen.jquery.min.js',
				to: 'assets/js/vendor/chosen.jquery.min.js',
			},
			{
				from: './node_modules/chart.js/dist/Chart.min.js',
				to: 'assets/js/vendor/chartjs.min.js',
			},
			{
				from: './node_modules/flot/jquery.flot.js',
				to: 'assets/js/vendor/jquery.flot.min.js',
				transform( content, src ) {
					return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
				}
			},
			{
				from: './node_modules/flot/jquery.flot.pie.js',
				to: 'assets/js/vendor/jquery.flot.pie.min.js',
				transform( content, src ) {
					return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
				}
			},
			{
				from: './node_modules/jquery-colorbox/jquery.colorbox-min.js',
				to: 'assets/js/vendor/jquery.colorbox.min.js',
			},
			{
				from: './node_modules/moment/moment.js',
				to: 'assets/js/vendor/moment.js.min.js',
				transform( content, src ) {
					return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
				}
			},
		] ),
	],
};

if ( config.mode !== 'production' ) {
	config.devtool = process.env.SOURCEMAP || 'source-map';
}

module.exports = config;
