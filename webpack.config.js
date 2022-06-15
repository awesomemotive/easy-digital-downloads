/**
 * External dependencies
 */
const path = require( 'path' );
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

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
	'upgrades',
];

// Webpack configuration.
const config = {
	...defaultConfig,
	resolve: {
		...defaultConfig.resolve,
		modules: [
			`${ __dirname }/assets/js`,
			'node_modules',
		],

		// Alias faked packages. One day these may be published...
		alias: {
			...defaultConfig.resolve.alias,
			'@easy-digital-downloads/currency': path.resolve( __dirname, 'assets/js/packages/currency/src/index.js' ),
		},
	},
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			...defaultConfig.optimization.splitChunks,
			// Default configuration does does funky things with cache groups
			// for entry points containing `-style`. Stop that to avoid changing
			// filenames incorrectly.
			cacheGroups: {}
		},
	},
	entry: {
		// Dynamic entry points for individual admin pages.
		...adminPages.reduce( ( memo, path ) => {
			memo[ `edd-admin-${ path.replace( '/', '-' ) }` ] = `./assets/js/admin/${ path }`;
			return memo;
		}, {} ),

		// Static admin pages.
		'edd-admin': './assets/js/admin',
		'edd-admin-tax-rates': './assets/js/admin/settings/tax-rates',
		'edd-admin-email-tags': './assets/js/admin/settings/email-tags',
		'edd-admin-extension-manager': './assets/js/admin/settings/extension-manager',
		'edd-admin-notices': './assets/js/admin/notices',

		// Front-end JavaScript.
		'edd-ajax': './assets/js/frontend/edd-ajax.js',
		'edd-checkout-global': './assets/js/frontend/checkout',
		'paypal-checkout': './assets/js/frontend/gateways/paypal.js',

		// Admin styles.
		'edd-admin-style': './assets/css/admin/style.scss',
		'edd-admin-chosen-style': './assets/css/admin/chosen/style.scss',
		'edd-admin-datepicker-style': './assets/css/admin/datepicker.scss',
		'edd-admin-email-tags-style': './assets/css/admin/email-tags.scss',
		'edd-admin-menu-style': './assets/css/admin/menu.scss',
		'edd-admin-tax-rates-style': './assets/css/admin/tax-rates/style.scss',
		'edd-admin-extension-manager-style': './assets/css/admin/extension-manager.scss'
	},
	output: {
		filename: 'assets/js/[name].js',
		path: __dirname,
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
	},
	plugins: [
		new MiniCSSExtractPlugin( {
			filename: ( { chunk } ) =>
				`assets/css/${ chunk.name.replace( '-style', '' ) }.min.css`,
		} ),
		new RemoveEmptyScriptsPlugin(),
		new WebpackRTLPlugin( {
			filename: [ /(\.min\.css)/i, '-rtl$1' ],
		} ),
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery'
		} ),
		// Copy vendor files to ensure 3rd party plugins relying on a script
		// handle to exist continue to be enqueued.
		new CopyWebpackPlugin( {
			patterns: [
				// Styles.
				{
					from: 'assets/css/vendor',
					to: 'assets/css',
				},

				// Scripts.
				{
					from: './node_modules/chart.js/dist/Chart.min.js',
					to: 'assets/js/vendor/chartjs.min.js',
				},
				{
					from: './node_modules/flot/jquery.flot.js',
					to: 'assets/js/vendor/jquery.flot.min.js',
				},
				{
					from: './node_modules/flot/jquery.flot.time.js',
					to: 'assets/js/vendor/jquery.flot.time.min.js',
				},
				{
					from: './node_modules/flot/jquery.flot.pie.js',
					to: 'assets/js/vendor/jquery.flot.pie.min.js',
				},
				{
					from: './node_modules/moment/moment.js',
					to: 'assets/js/vendor/moment.min.js',
				},
				{
					from: './node_modules/jquery-creditcardvalidator/jquery.creditCardValidator.js',
					to: 'assets/js/vendor/jquery.creditcardvalidator.min.js',
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
			]
		} ),
	],
};

module.exports = config;
