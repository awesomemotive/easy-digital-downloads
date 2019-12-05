/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const UglifyJS = require( 'uglify-es' );

const adminPages = [
	'customers',
	'dashboard',
	'discounts',
	'downloads',
	'tools/export',
	'tools/import',
	'notes',
	'orders',
	'reports',
	'payments',
	'settings',
	'tools',
];

const minifyJs = ( content ) => {
	return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
};

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	resolve: {
		modules: [
			`${ __dirname }/assets/js`,
			'node_modules',
		],
	},
	entry: Object.assign(
		// Dynamic entry points for individual admin pages.
		adminPages.reduce( ( memo, path ) => {
			memo[ `edd-admin-${ path.replace( '/', '-' ) }` ] = `./assets/js/admin/${ path }`;
			return memo;
		}, {} ),
		{
			'edd-admin': './assets/js/admin',
			'edd-admin-backwards-compatibility': './assets/js/admin/backwards-compatibility.js',
			'edd-admin-tax-rates': './assets/js/admin/settings/tax-rates',
			'edd-admin-email-tags': './assets/js/admin/settings/email-tags',
			'edd-ajax': './assets/js/frontend/edd-ajax.js',
			'edd-checkout-global': './assets/js/frontend/checkout',
		}
	),
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
			jQuery: 'jquery'
		} ),
		// Copy vendor files to ensure 3rd party plugins relying on a script
		// handle to exist continue to be enqueued.
		new CopyWebpackPlugin( [
			{
				from: './node_modules/chosen-js/chosen.jquery.min.js',
				to: 'assets/js/vendor/jquery.chosen.min.js',
			},
			{
				from: './node_modules/chart.js/dist/Chart.min.js',
				to: 'assets/js/vendor/chartjs.min.js',
			},
			{
				from: './node_modules/flot/jquery.flot.js',
				to: 'assets/js/vendor/jquery.flot.min.js',
				transform: ( content ) => minifyJs( content ),
			},
			{
				from: './node_modules/flot/jquery.flot.time.js',
				to: 'assets/js/vendor/jquery.flot.time.min.js',
				transform: ( content ) => minifyJs( content ),
			},
			{
				from: './node_modules/flot/jquery.flot.pie.js',
				to: 'assets/js/vendor/jquery.flot.pie.min.js',
				transform: ( content ) => minifyJs( content ),
			},
			{
				from: './node_modules/moment/moment.js',
				to: 'assets/js/vendor/moment.min.js',
				transform: ( content ) => minifyJs( content ),
			},
			{
				from: './node_modules/jquery-creditcardvalidator/jquery.creditCardValidator.js',
				to: 'assets/js/vendor/jquery.creditcardvalidator.min.js',
				transform: ( content ) => minifyJs( content ),
			},
			{
				from: './node_modules/jquery-validation/dist/jquery.validate.min.js',
				// This file is not registered in EDD so the URL must remain the same.
				to: 'assets/js/jquery.validate.min.js',
			},
			{
				from: './node_modules/jquery.payment/lib/jquery.payment.min.js',
				to: 'assets/js/vendor/jquery.payment.min.js',
			},
		] ),
	],
};

if ( config.mode !== 'production' ) {
	config.devtool = process.env.SOURCEMAP || 'source-map';
}

module.exports = config;
