/**
 * External dependencies
 */
const path = require( 'path' );
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const UglifyJS = require( 'uglify-es' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );
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
];

const minifyJs = ( content ) => {
	return Promise.resolve( Buffer.from( UglifyJS.minify( content.toString() ).code ) );
};

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
	entry: {
		// Dynamic entry points for individual admin pages.
		...adminPages.reduce( ( memo, path ) => {
			memo[ `edd-admin-${ path.replace( '/', '-' ) }` ] = `./assets/js/admin/${ path }`;
			return memo;
		}, {} ),

		// Static admin pages.
		'edd-admin': './assets/js/admin',
		'edd-admin-backwards-compatibility': './assets/js/admin/backwards-compatibility.js',
		'edd-admin-tax-rates': './assets/js/admin/settings/tax-rates',
		'edd-admin-email-tags': './assets/js/admin/settings/email-tags',
		'edd-ajax': './assets/js/frontend/edd-ajax.js',
		'edd-checkout-global': './assets/js/frontend/checkout',

		// Admin styles.
		'edd-admin-style': './assets/css/admin/style.scss',
		'edd-admin-chosen-style': './assets/css/admin/chosen/style.scss',
		'edd-admin-datepicker-style': './assets/css/admin/datepicker.scss',
		'edd-admin-email-tags-style': './assets/css/admin/email-tags.scss',
		'edd-admin-menu-style': './assets/css/admin/menu.scss',
		'edd-admin-tax-rates-style': './assets/css/admin/tax-rates/style.scss',
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
			esModule: false,
			moduleFilename: ( chunk ) =>
				`assets/css/${ chunk.name.replace( '-style', '' ) }.min.css`
		} ),
		new WebpackRTLPlugin( {
			filename: [ /(\.min\.css)/i, '-rtl$1' ],
		} ),
		new FixStyleOnlyEntriesPlugin(),
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
			]
		} ),
	],
};

// Remove automatic split of style- imports.
// @link https://github.com/WordPress/gutenberg/blob/master/packages/scripts/config/webpack.config.js#L67-L77
delete config.optimization.splitChunks.cacheGroups;

module.exports = config;
