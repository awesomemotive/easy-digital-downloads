module.exports = function( grunt ) {
	// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		checktextdomain: {
			options: {
				text_domain: 'easy-digital-downloads',
				correct_domain: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d',
				],
			},
			files: {
				src: [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/**', // Exclude build/
				],
				expand: true,
			},
		},

		// Clean up build directory
		clean: {
			main: [ 'build/<%= pkg.name %>' ],
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src: [
					'assets/sample-products-import.xml',
					'assets/css/*.min.css',
					'assets/css/admin/style.css',
					'assets/js/*.js',
					'assets/js/*.min.js',
					'assets/js/vendor/**',
					'assets/images/**',
					'includes/**',
					'languages/**',
					'templates/**',
					'*.php',
					'*.txt',
				],
				dest: 'build/<%= pkg.name %>/',
			},
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},

		replace: {
			stripe: {
				options: {
					patterns: [
						{
							match: /edd_stripe_bootstrap/g,
							replacement: 'edd_stripe_core_bootstrap',
							expression: true,
						},
						{
							match: /remove_action(.*);/g,
							replacement: '',
							expression: true,
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'includes/gateways/stripe/edd-stripe.php' ],
						dest: 'includes/gateways/stripe'
					}
				]
			},
			blocks: {
				options: {
					patterns: [
						{
							match: /init_blocks/g,
							replacement: 'init_core_blocks',
							expression: true,
						},
						{
							match: /update_required_pages/g,
							replacement: 'update_core_required_pages',
							expression: true,
						},
						{
							match: /remove_action(.*);/g,
							replacement: '',
							expression: true,
						},
						{
							match: /remove_filter(.*);/g,
							replacement: '',
							expression: true,
						},
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'includes/blocks/edd-blocks.php' ],
						dest: 'includes/blocks'
					}
				]
			}
		}
	} );

	// Build task(s).
	grunt.registerTask( 'prep', [ 'force:checktextdomain', 'clean', 'replace' ] );
	grunt.registerTask( 'build', [ 'copy', 'compress' ] );
};
