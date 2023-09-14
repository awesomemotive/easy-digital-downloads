<?php
/**
 * Requirements
 *
 * The class to check the requirements and load the plugin if met.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license   GPL2+
 * @since     3.0
 */

final class EDD_Requirements_Check {

	/**
	 * Plugin file
	 *
	 * @since 3.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Plugin basename
	 *
	 * @since 3.0
	 * @var string
	 */
	private $base = '';

	/**
	 * Requirements array
	 *
	 * @todo Extend WP_Dependencies
	 * @var array
	 * @since 3.0
	 */
	private $requirements = array(

		// PHP
		'php' => array(
			'minimum' => '7.4',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),

		// WordPress
		'wp'  => array(
			'minimum' => '5.8',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),
	);

	/**
	 * Setup plugin requirements
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Setup file & base
		$this->file = EDD_PLUGIN_FILE;
		$this->base = EDD_PLUGIN_BASE;

		// Load or quit
		$this->met()
			? $this->load()
			: $this->quit();
	}

	/**
	 * Quit without loading
	 *
	 * @since 3.0
	 */
	private function quit() {
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_filter( "plugin_action_links_{$this->base}", array( $this, 'plugin_row_links' ) );
		add_action( "after_plugin_row_{$this->base}", array( $this, 'plugin_row_notice' ) );
	}

	/** Specific Methods ******************************************************/

	/**
	 * Load normally
	 *
	 * @since 3.0
	 */
	private function load() {

		require_once dirname( $this->file ) . '/vendor/autoload.php';

		// Maybe include the bundled bootstrapper
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			require_once dirname( $this->file ) . '/includes/class-easy-digital-downloads.php';
		}

		// Maybe hook-in the bootstrapper
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {

			// Bootstrap to plugins_loaded before priority 10 to make sure
			// add-ons are loaded after us.
			add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 4 );

			// Register the activation hook
			register_activation_hook( $this->file, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 3.0
	 */
	public function install() {

		// Bootstrap to include all of the necessary files.
		$this->bootstrap();

		// Network wide?
		$network_wide = ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;

		// Call the installer directly during the activation hook.
		edd_install( $network_wide );
	}

	/**
	 * Bootstrap everything.
	 *
	 * @since 3.0
	 */
	public function bootstrap() {
		\Easy_Digital_Downloads::instance( $this->file );
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_url() {
		return 'https://easydigitaldownloads.com/recommended-wordpress-hosting/';
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'easy-digital-downloads' );
	}

	/**
	 * Plugin specific text to describe a single unmet requirement.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %1$s (%2$s), but (%3$s) is installed.', 'easy-digital-downloads' );
	}

	/**
	 * Plugin specific text to describe a single missing requirement.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %1$s (%2$s), but it appears to be missing.', 'easy-digital-downloads' );
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'easy-digital-downloads' );
	}

	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_label() {
		return esc_html__( 'Easy Digital Download Requirements', 'easy-digital-downloads' );
	}

	/**
	 * Plugin specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function unmet_requirements_name() {
		return 'edd-requirements';
	}

	/** Agnostic Methods ******************************************************/

	/**
	 * Plugin agnostic method to output the additional plugin row
	 *
	 * @since 3.0
	 */
	public function plugin_row_notice() {
		// wp_is_auto_update_enabled_for_type was introduced in WordPress 5.5.
		$colspan = function_exists( 'wp_is_auto_update_enabled_for_type' ) && wp_is_auto_update_enabled_for_type( 'plugin' ) ? 2 : 1;
		?>
		<tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
			<th class="check-column">
				<span class="dashicons dashicons-warning"></span>
			</th>
			<td class="column-primary">
				<?php $this->unmet_requirements_text(); ?>
			</td>
			<td class="column-description" colspan="<?php echo esc_attr( $colspan ); ?>">
				<?php $this->unmet_requirements_description(); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 * @since 3.0
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Plugin agnostic method to output specific unmet requirement information
	 *
	 * @since 3.0
	 * @param array $requirement
	 */
	private function unmet_requirement_description( $requirement = array() ) {

		// Requirement exists, but is out of date
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);

			// Requirement could not be found
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}

		// Output the description
		echo '<p>' . $text . '</p>';
	}

	/**
	 * Plugin agnostic method to output unmet requirements styling
	 *
	 * @since 3.0
	 */
	public function admin_head() {

		// Get the requirements row name
		$name = $this->unmet_requirements_name();
		?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions
	 *
	 * @since 3.0
	 * @param array $links
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';

		// Return links with Requirements link
		return $links;
	}

	/** Checkers **************************************************************/

	/**
	 * Plugin specific requirements checker
	 *
	 * @since 3.0
	 */
	private function check() {

		// Loop through requirements
		foreach ( $this->requirements as $dependency => $properties ) {

			// Which dependency are we checking?
			switch ( $dependency ) {

				// PHP
				case 'php':
					$version = phpversion();
					break;

				// WP
				case 'wp':
					$version = get_bloginfo( 'version' );
					break;

				// Unknown
				default:
					$version = false;
					break;
			}

			// Merge to original array
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge(
					$this->requirements[ $dependency ],
					array(
						'current' => $version,
						'checked' => true,
						'met'     => version_compare( $version, $properties['minimum'], '>=' ),
					)
				);
			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	public function met() {

		// Run the check
		$this->check();

		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		// Look for unmet dependencies, and exit if so
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				return false;
			}
		}

		return true;
	}

	/** Translations **********************************************************/

	/**
	 * Plugin specific text-domain loader.
	 *
	 * @deprecated 3.1.1.3. Since EDD no longer bundles any language files,
	 * and WordPress Core automatically loads the custom wp-content/languages/easy-digital-downloads/.mo file if it's found,
	 * this is no longer needed.
	 * @since 1.4
	 * @return void
	 */
	public function load_textdomain() {

		/*
		 * Due to the introduction of language packs through translate.wordpress.org,
		 * loading our textdomain is complex.
		 *
		 * In v2.4.6, our textdomain changed from "edd" to "easy-digital-downloads".
		 *
		 * To support existing translation files from before the change, we must
		 * look for translation files in several places and under several names.
		 *
		 * - wp-content/languages/plugins/easy-digital-downloads (introduced with language packs)
		 * - wp-content/languages/edd/ (custom folder we have supported since 1.4)
		 * - wp-content/plugins/easy-digital-downloads/languages/
		 *
		 * In wp-content/languages/edd/ we must look for:
		 * - "easy-digital-downloads-{lang}_{country}.mo"
		 *
		 * In wp-content/languages/edd/ we must look for:
		 * - "edd-{lang}_{country}.mo" as that was the old file naming convention
		 *
		 * In wp-content/languages/plugins/easy-digital-downloads/ we only need to look for:
		 * - "easy-digital-downloads-{lang}_{country}.mo" as that is the new structure
		 *
		 * In wp-content/plugins/easy-digital-downloads/languages/, we must look for:
		 * - both naming conventions. This is done by filtering "load_textdomain_mofile"
		 */
		add_filter( 'load_textdomain_mofile', array( $this, 'load_old_textdomain' ), 10, 2 );

		// Set filter for plugin's languages directory.
		$edd_lang_dir = dirname( $this->base ) . '/languages/';
		$edd_lang_dir = apply_filters( 'edd_languages_directory', $edd_lang_dir );

		unload_textdomain( 'easy-digital-downloads' );

		/**
		 * Defines the plugin language locale used in Easy Digital Downloads.
		 *
		 * @var $get_locale The locale to use.
		 */
		$locale = apply_filters( 'plugin_locale', get_user_locale(), 'easy-digital-downloads' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'easy-digital-downloads', $locale );

		// Look for wp-content/languages/edd/easy-digital-downloads-{lang}_{country}.mo
		$mofile_global1 = WP_LANG_DIR . "/edd/easy-digital-downloads-{$locale}.mo";

		// Look for wp-content/languages/edd/edd-{lang}_{country}.mo
		$mofile_global2 = WP_LANG_DIR . "/edd/edd-{$locale}.mo";

		// Look in wp-content/languages/plugins/easy-digital-downloads
		$mofile_global3 = WP_LANG_DIR . "/plugins/easy-digital-downloads/{$mofile}";

		// Try to load from first global location
		if ( file_exists( $mofile_global1 ) ) {
			load_textdomain( 'easy-digital-downloads', $mofile_global1 );

			// Try to load from next global location
		} elseif ( file_exists( $mofile_global2 ) ) {
			load_textdomain( 'easy-digital-downloads', $mofile_global2 );

			// Try to load from next global location
		} elseif ( file_exists( $mofile_global3 ) ) {
			load_textdomain( 'easy-digital-downloads', $mofile_global3 );

			// Load the default language files
		} else {
			load_plugin_textdomain( 'easy-digital-downloads', false, $edd_lang_dir );
		}
	}

	/**
	 * Load a .mo file for the old textdomain if one exists.
	 *
	 * @deprecated 3.1.1.3
	 * @see https://github.com/10up/grunt-wp-plugin/issues/21#issuecomment-62003284
	 */
	public function load_old_textdomain( $mofile, $textdomain ) {

		// Fallback for old text domain
		if ( ( 'easy-digital-downloads' === $textdomain ) && ! file_exists( $mofile ) ) {
			$mofile = dirname( $mofile ) . DIRECTORY_SEPARATOR . str_replace( $textdomain, 'edd', basename( $mofile ) );
		}

		// Return (possibly overridden) mofile
		return $mofile;
	}
}
