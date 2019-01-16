/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const UglifyJS = require( 'uglify-es' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );

/**
 * Minify a string of Javascript.
 * This does not transform anything, it simply removes whitespace.
 *
 * @param {string} content Content to minify.
 * @return string
 */
const minifyJs = ( content ) => {
	return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
};

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	resolve: {
		modules: [
			`${ __dirname }/assets`,
			'node_modules',
		],
	},
	entry: Object.assign(
		// Admin Javascript.
		{
			'edd-admin': './assets/js/admin',
		},
		// Dynamic entry points for individual admin pages.
		// These are transformed to a standard `edd-admin-${ entry }` format.
		[
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
			'settings/tax-rates',
			'settings/email-tags',
			'tools',
			'backwards-compatibility',
		].reduce( ( memo, path ) => {
			memo[ `edd-admin-${ path.replace( '/', '-' ) }` ] = `./assets/js/admin/${ path }`;
			return memo;
		}, {} ),

		// Frontend Javascript.
		{
			'edd-ajax': './assets/js/frontend/edd-ajax.js',
			'edd-checkout-global': './assets/js/frontend/checkout',
		},
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
		// Copy vendor files to ensure 3rd party plugins relying on a script
		// handle to exist continue to be enqueued.
		new CopyWebpackPlugin( [
			{
				from: './node_modules/chosen-js/chosen.jquery.min.js',
				to: 'assets/js/vendor/jquery.chosen.min.js',
			},
			{
				from: './node_modules/chosen-js/chosen.min.css',
				to: 'assets/css/chosen.css',
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
		new WebpackRTLPlugin( {
			suffix: '-rtl',
			minify: process.env.NODE_ENV === 'production' ? { safe: true } : false,
		} ),
	],
};

// Configuration for the ExtractTextPlugin.
const extractConfig = {
	use: [
		{
			loader: 'raw-loader',
		},
		{
			loader: 'postcss-loader',
			options: {
				plugins: [
					require( 'autoprefixer' ),
				],
			},
		},
	],
};

// Extract Admin CSS and put in the correct place.
[
	'style',
	'chosen',
	'menu',
	'datepicker',
	'settings-email-tags',
	'settings-tax-rates',
].forEach( ( name ) => {
	const file = new ExtractTextPlugin( {
		filename: `./assets/css/edd-admin-${ name }.css`,
	} );

	const rule = {
		test: new RegExp( `${ name }\.css$` ),
		use: file.extract( extractConfig ),
		include: /css/,
	};

	config.plugins.push( file );
	config.module.rules.push( rule );
} );

if ( config.mode !== 'production' ) {
	config.devtool = process.env.SOURCEMAP || 'source-map';
}

module.exports = config;
