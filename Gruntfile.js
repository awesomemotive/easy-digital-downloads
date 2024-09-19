module.exports = function ( grunt ) {
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
					'!vendor/**', // Exclude vendor/
					'!tests/**', // Exclude tests/
					'!includes/blocks/node_modules/**', // Exclude includes/blocks/node_modules/
					'!includes/libraries/**', // Exclude includes/libraries/
				],
				expand: true,
			},
		},

		// Clean up build directory
		clean: {
			main: [ 'build/**' ],
			repo: [ 'build/<%= pkg.name %>-public/**' ],
		},

		// Copy the plugin into the build directory
		copy: {
			pro: {
				src: [
					'assets/**',
					'!assets/lite/**',
					'i18n/**',
					'includes/**',
					'!includes/blocks/node_modules/**',
					'!includes/blocks/composer.json',
					'!includes/blocks/package.json',
					'!includes/blocks/package-lock.json',
					'languages/**',
					'libraries/**',
					'templates/**',
					'src/**',
					'!src/Lite/**',
					'*.php',
					'*.txt',
					'!vendor/**',
					'vendor/autoload.php',
					'vendor/composer/**',
					'vendor/symfony/deprecation-contracts/**',
					'vendor/symfony/polyfill-php80/**',
					'vendor/symfony/polyfill-mbstring/**',
				],
				dest: 'build/<%= pkg.name %>-pro/',
			},
			lite: {
				src: [
					'assets/**',
					'!assets/pro/**',
					'i18n/**',
					'includes/**',
					'!includes/blocks/pro/**',
					'!includes/blocks/assets/pro/**',
					'!includes/blocks/build/pro/**',
					'!includes/blocks/node_modules/**',
					'!includes/blocks/src/pro/**',
					'!includes/blocks/composer.json',
					'!includes/blocks/package.json',
					'!includes/blocks/package-lock.json',
					'languages/**',
					'libraries/**',
					'templates/**',
					'src/**',
					'*.php',
					'*.txt',
					'!src/Pro/**',
					'!vendor/**',
					'vendor/autoload.php',
					'vendor/composer/**',
					'vendor/symfony/deprecation-contracts/**',
					'vendor/symfony/polyfill-php80/**',
					'vendor/symfony/polyfill-mbstring/**',
				],
				dest: 'build/<%= pkg.name %>/',
			},
			repo: {
				src: [
					'**',
					'assets/**',
					'!assets/pro/**',
					'!build/**',
					'i18n/**',
					'includes/**',
					'!includes/blocks/pro/**',
					'!includes/blocks/assets/pro/**',
					'!includes/blocks/build/pro/**',
					'!includes/blocks/node_modules/**',
					'!includes/blocks/src/pro/**',
					'languages/**',
					'libraries/**',
					'!node_modules/**',
					'templates/**',
					'src/**',
					'!src/Pro/**',
					'!vendor/**',
					'vendor/autoload.php',
					'vendor/composer/**',
					'vendor/symfony/deprecation-contracts/**',
					'vendor/symfony/polyfill-php80/**',
					'vendor/symfony/polyfill-mbstring/**',
				],
				dest: 'build/<%= pkg.name %>-public/',
			}
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			pro: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>-pro-<%= pkg.version %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>-pro/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>-pro/',
			},
			lite: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>-<%= pkg.version %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},

		replace: {
			pro: {
				options: {
					patterns: [
						{
							match: /Plugin Name: Easy Digital Downloads \(Pro\)/g,
							replacement: 'Plugin Name: Easy Digital Downloads',
							expression: true,
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'build/easy-digital-downloads/easy-digital-downloads.php' ],
						dest: 'build/easy-digital-downloads'
					}
				]
			},
			repo: {
				options: {
					patterns: [
						{
							match: /Plugin Name: Easy Digital Downloads \(Pro\)/g,
							replacement: 'Plugin Name: Easy Digital Downloads',
							expression: true,
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: [ 'build/easy-digital-downloads-public/easy-digital-downloads.php' ],
						dest: 'build/easy-digital-downloads-public'
					}
				]
			}
		}
	} );

	// Build task(s).
	grunt.registerTask( 'prep', [ 'clean', 'force:checktextdomain' ] );
	grunt.registerTask( 'build', [ 'clean', 'pro', 'lite' ] );
	grunt.registerTask( 'pro', [ 'copy:pro', 'compress:pro' ] );
	grunt.registerTask( 'lite', [ 'copy:lite', 'replace:pro' ] );
	grunt.registerTask( 'repo', [ 'clean:repo', 'copy:repo', 'replace:repo' ] );
};
