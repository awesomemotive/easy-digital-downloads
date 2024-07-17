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
	'onboarding',
	'orders',
	'reports',
	'payments',
	'settings',
	'tools',
	'upgrades',
	'flyout',
	'notifications',
	'emails/list-table',
	'emails/editor',
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
			memo[ `js/edd-admin-${ path.replace( '/', '-' ) }` ] = `./assets/js/admin/${ path }`;
			return memo;
		}, {} ),

		// Static admin pages.
		'js/edd-admin': './assets/js/admin',
		'js/edd-admin-tax-rates': './assets/js/admin/settings/tax-rates',
		'js/edd-admin-email-tags': './assets/js/admin/settings/email-tags',
		'js/edd-admin-extension-manager': './assets/js/admin/settings/extension-manager',
		'js/edd-admin-notices': './assets/js/admin/notices',
		'js/edd-admin-pass-handler': './assets/js/admin/settings/pass-handler',
		'js/edd-admin-onboarding': './assets/js/admin/onboarding',
		'js/edd-admin-licensing': './assets/js/admin/settings/licensing',
		'js/edd-admin-pointers': './assets/js/admin/pointers',
		'js/edd-admin-downloads-editor': './assets/js/admin/downloads/editor',
		'js/stripe-admin': './assets/js/admin/stripe/index.js',
		'js/stripe-notices': './assets/js/admin/stripe/notices.js',

		// Front-end JavaScript.
		'js/edd-ajax': './assets/js/frontend/edd-ajax.js',
		'js/edd-checkout-global': './assets/js/frontend/checkout',
		'js/paypal-checkout': './assets/js/frontend/gateways/paypal.js',
		'js/stripe-cardelements': './assets/js/frontend/gateways/stripe/loader/card-elements.js',
		'js/stripe-paymentelements': './assets/js/frontend/gateways/stripe/loader/payment-elements.js',

		'pro/js/checkout': './assets/pro/js/frontend/checkout.js',
		'pro/js/duplicator': './assets/pro/js/admin/duplicator.js',

		// Admin styles.
		'edd-admin-style': './assets/css/admin/style.scss',
		'edd-admin-chosen-style': './assets/css/admin/chosen/style.scss',
		'edd-admin-datepicker-style': './assets/css/admin/datepicker.scss',
		'edd-admin-email-tags-style': './assets/css/admin/email-tags.scss',
		'edd-admin-menu-style': './assets/css/admin/menu.scss',
		'edd-admin-tax-rates-style': './assets/css/admin/tax-rates/style.scss',
		'edd-admin-extension-manager-style': './assets/css/admin/extension-manager.scss',
		'edd-admin-pass-handler-style': './assets/css/admin/pass-handler.scss',
		'edd-admin-onboarding-style': './assets/css/admin/onboarding/style.scss',
		'stripe-admin': './assets/css/admin/gateways/stripe.scss',
		'edd-admin-notifications-style': './assets/css/admin/notifications/style.scss',
		'edd-admin-emails-style': './assets/css/admin/emails/style.scss',
		'edd-admin-pointers-style': './assets/css/admin/pointers/style.scss',

		'edd-style': './assets/css/frontend/style.scss',
		'stripe-cardelements': './assets/css/frontend/stripe/card-elements.scss',
		'stripe-paymentelements': './assets/css/frontend/stripe/payment-elements.scss',
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets' ),
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
	},
	plugins: [
		new MiniCSSExtractPlugin( {
			esModule: false,
			moduleFilename: ( chunk ) =>
				`css/${ chunk.name.replace( '-style', '' ) }.min.css`
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
					to: 'css',
				},

				// Scripts.
				{
					from: './node_modules/chart.js/dist/Chart.min.js',
					to: 'js/vendor/chartjs.min.js',
				},
				{
					from: './node_modules/flot/jquery.flot.js',
					to: 'js/vendor/jquery.flot.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/flot/jquery.flot.time.js',
					to: 'js/vendor/jquery.flot.time.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/flot/jquery.flot.pie.js',
					to: 'js/vendor/jquery.flot.pie.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/moment/moment.js',
					to: 'js/vendor/moment.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/moment-timezone/moment-timezone.js',
					to: 'js/vendor/moment-timezone.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/jquery-creditcardvalidator/jquery.creditCardValidator.js',
					to: 'js/vendor/jquery.creditcardvalidator.min.js',
					transform: ( content ) => minifyJs( content ),
				},
				{
					from: './node_modules/jquery-validation/dist/jquery.validate.min.js',
					// This file is not registered in EDD so the URL must remain the same.
					to: 'js/jquery.validate.min.js',
				},
				{
					from: './node_modules/jquery.payment/lib/jquery.payment.min.js',
					to: 'js/vendor/jquery.payment.min.js',
				},
			]
		} ),
	],
};

// Remove automatic split of style- imports.
// @link https://github.com/WordPress/gutenberg/blob/master/packages/scripts/config/webpack.config.js#L67-L77
delete config.optimization.splitChunks.cacheGroups;

module.exports = config;
