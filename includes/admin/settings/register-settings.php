<?php
/**
 * Register Settings
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.8.4
 * @global $edd_options Array of all the EDD Options
 * @return mixed
 */
function edd_get_option( $key = '', $default = false ) {
	global $edd_options;

	$value = $default;

	if ( isset( $edd_options[ $key ] ) ) {
		if ( is_numeric( $edd_options[ $key ] ) ) {
			$value = $edd_options[ $key ];
		} else {
			$value = ! empty( $edd_options[ $key ] ) ? $edd_options[ $key ] : $default;
		}
	}

	$value = apply_filters( 'edd_get_option', $value, $key, $default );

	return apply_filters( 'edd_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an edd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the edd_options array.
 *
 * @since 2.3
 *
 * @param string          $key         The Key to update
 * @param string|bool|int $value       The value to set the key to
 *
 * @global                $edd_options Array of all the EDD Options
 * @return boolean True if updated, false if not.
 */
function edd_update_option( $key = '', $value = false ) {
	global $edd_options;

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = edd_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'edd_settings' );
	if ( empty( $options ) ) {
		$options = array();
	}

	// Let's let devs alter that value coming in
	$value = apply_filters( 'edd_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		$edd_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an edd setting value in both the db and the global variable.
 *
 * @since 2.3
 *
 * @param string $key         The Key to delete
 *
 * @global       $edd_options Array of all the EDD Options
 * @return boolean True if removed, false if not.
 */
function edd_delete_option( $key = '' ) {
	global $edd_options;

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'edd_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	// Remove this option from the global EDD settings to the array_merge in edd_settings_sanitize() doesn't re-add it.
	if ( isset( $edd_options[ $key ] ) ) {
		unset( $edd_options[ $key ] );
	}

	$did_update = update_option( 'edd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		$edd_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array EDD settings
 */
function edd_get_settings() {

	// Get the option key
	$settings = get_option( 'edd_settings' );

	// Look for old option keys
	if ( empty( $settings ) ) {
		$settings = array();

		// Old option keys
		$old_keys = array(
			'edd_settings_general',
			'edd_settings_gateways',
			'edd_settings_emails',
			'edd_settings_styles',
			'edd_settings_taxes',
			'edd_settings_extensions',
			'edd_settings_licenses',
			'edd_settings_misc',
		);

		// Merge old keys together
		foreach ( $old_keys as $key ) {
			$settings[ $key ] = get_option( $key, array() );
		}

		// Remove empties
		$settings = array_filter( array_values( $settings ) );

		// Update the main option
		update_option( 'edd_settings', $settings );
	}

	// Filter & return
	return apply_filters( 'edd_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function edd_register_settings() {

	// Get registered settings
	$edd_settings = edd_get_registered_settings();

	// Loop through settings
	foreach ( $edd_settings as $tab => $sections ) {

		// Loop through sections
		foreach ( $sections as $section => $settings ) {

			// Check for backwards compatibility
			$section_tabs = edd_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section  = 'main';
				$settings = $sections;
			}

			// Current page
			$page = "edd_settings_{$tab}_{$section}";

			// Add the settings section
			add_settings_section(
				$page,
				__return_null(),
				'__return_false',
				$page
			);

			foreach ( $settings as $option ) {

				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				// Parse args
				$args = wp_parse_args( $option, array(
					'section'       => $section,
					'id'            => null,
					'desc'          => '',
					'name'          => '',
					'size'          => null,
					'options'       => '',
					'std'           => '',
					'min'           => null,
					'max'           => null,
					'step'          => null,
					'chosen'        => null,
					'multiple'      => null,
					'placeholder'   => null,
					'allow_blank'   => true,
					'readonly'      => false,
					'faux'          => false,
					'tooltip_title' => false,
					'tooltip_desc'  => false,
					'field_class'   => '',
					'label_for'     => false
				) );

				// Callback fallback
				$func     = 'edd_' . $args['type'] . '_callback';
				$callback = ! function_exists( $func )
					? 'edd_missing_callback'
					: $func;

				// Link the label to the form field
				if ( empty( $args['label_for'] ) ) {
					$args['label_for'] = 'edd_settings[' . $args['id'] . ']';
				}

				// Add the settings field
				add_settings_field(
					'edd_settings[' . $args['id'] . ']',
					$args['name'],
					$callback,
					$page,
					$page,
					$args
				);
			}
		}
	}

	// Register our setting in the options table
	register_setting( 'edd_settings', 'edd_settings', 'edd_settings_sanitize' );
}
add_action( 'admin_init', 'edd_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @since 3.0 Use a static variable internally to store registered settings
 * @return array
 */
function edd_get_registered_settings() {
	static $edd_settings = null;

	// Only build settings if not already built.
	if ( null === $edd_settings ) {
		$settings     = new EDD\Admin\Settings\Register();
		$edd_settings = $settings->get();
	}

	return $edd_settings;
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.8.2
 *
 * @param array  $input       The value inputted in the field
 *
 * @global array $edd_options Array of all the EDD Options
 *
 * @return array $input Sanitized value
 */
function edd_settings_sanitize( $input = array() ) {
	global $edd_options;

	// Default values
	$referrer      = '';
	$setting_types = edd_get_registered_settings_types();
	$doing_section = ! empty( $_POST['_wp_http_referer'] );
	$input         = ! empty( $input )
		? $input
		: array();

	if ( true === $doing_section ) {

		// Pull out the tab and section
		parse_str( $_POST['_wp_http_referer'], $referrer );
		$tab     = ! empty( $referrer['tab']     ) ? sanitize_key( $referrer['tab']     ) : 'general';
		$section = ! empty( $referrer['section'] ) ? sanitize_key( $referrer['section'] ) : 'main';

		if ( ! empty( $_POST['edd_tab_override'] ) ) {
			$tab = sanitize_text_field( $_POST['edd_tab_override'] );
		}

		// Maybe override the tab section
		if ( ! empty( $_POST['edd_section_override'] ) ) {
			$section = sanitize_text_field( $_POST['edd_section_override'] );
		}

		// Get setting types for this section
		$setting_types = edd_get_registered_settings_types( $tab, $section );

		// Run a general sanitization for the tab for special fields (like taxes)
		$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $input );

		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
		$input = apply_filters( 'edd_settings_' . $tab . '-' . $section . '_sanitize', $input );
	}

	// Remove non setting types and merge settings together
	$non_setting_types = edd_get_non_setting_types();
	$setting_types     = array_diff( $setting_types, $non_setting_types );
	$output            = array_merge( $edd_options, $input );

	// Loop through settings, and apply any filters
	foreach ( $setting_types as $key => $type ) {

		// Skip if type is empty
		if ( empty( $type ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {
			$output[ $key ] = apply_filters( 'edd_settings_sanitize_' . $type, $output[ $key ], $key );
			$output[ $key ] = apply_filters( 'edd_settings_sanitize', $output[ $key ], $key );
		}

		if ( true === $doing_section ) {
			switch ( $type ) {
				case 'checkbox':
				case 'checkbox_description':
				case 'gateways':
				case 'multicheck':
				case 'payment_icons':
					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
						unset( $output[ $key ] );
					}
					break;
				case 'text':
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
						unset( $output[ $key ] );
					}
					break;
				case 'number':
					if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) {
						unset( $output[ $key ] );
					}

					$setting_details = edd_get_registered_setting_details( $tab, $section, $key );
					$number_type     = ! empty( $setting_details['step'] ) && false !== strpos( $setting_details['step'], '.' ) ? 'floatval' : 'intval';
					$minimum         = isset( $setting_details['min'] ) ? $number_type( $setting_details['min'] ) : false;
					$maximum         = isset( $setting_details['max'] ) ? $number_type( $setting_details['max'] ) : false;
					$new_value       = $number_type( $input[ $key ] );

					if ( ( false !== $minimum && $minimum > $new_value ) || ( false !== $maximum && $maximum < $new_value ) ) {
						unset( $output[ $key ] );
					}
					break;
				default:
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
						unset( $output[ $key ] );
					}
					break;
			}
		} elseif ( empty( $input[ $key ] ) ) {
			unset( $output[ $key ] );
		}
	}

	// Return output.
	return (array) $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in edd_settings_sanitize
 *
 * @since  2.6.5
 * @since  2.8 - Added the ability to filter setting types by tab and section
 *
 * @param $filtered_tab     bool|string     A tab to filter setting types by.
 * @param $filtered_section bool|string A section to filter setting types by.
 *
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function edd_get_registered_settings_types( $filtered_tab = false, $filtered_section = false ) {
	$settings      = edd_get_registered_settings();
	$setting_types = array();

	foreach ( $settings as $tab_id => $tab ) {

		if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
			continue;
		}

		foreach ( $tab as $section_id => $section_or_setting ) {

			// See if we have a setting registered at the tab level for backwards compatibility
			if ( false !== $filtered_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
				$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
				continue;
			}

			if ( false !== $filtered_section && $filtered_section !== $section_id ) {
				continue;
			}

			foreach ( $section_or_setting as $section_settings ) {
				if ( ! empty( $section_settings['type'] ) ) {
					$setting_types[ $section_settings['id'] ] = $section_settings['type'];
				}
			}
		}
	}

	return $setting_types;
}

/**
 * Allow getting a specific setting's details.
 *
 * @since 3.0
 *
 * @param string $filtered_tab      The tab the setting's section is in.
 * @param string $filtered_section  The section the setting is located in.
 * @param string $setting_key       The key associated with the setting.
 *
 * @return array
 */
function edd_get_registered_setting_details( $filtered_tab = '', $filtered_section = '', $setting_key = '' ) {
	$settings        = edd_get_registered_settings();
	$setting_details = array();

	if ( isset( $settings[ $filtered_tab ][ $filtered_section][ $setting_key ] ) ) {
		$setting_details = $settings[ $filtered_tab ][ $filtered_section][ $setting_key ];
	}

	return $setting_details;
}

/**
 * Return array of settings field types that aren't settings.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_non_setting_types() {
	return apply_filters( 'edd_non_setting_types', array(
		'header',
		'descriptive_text',
		'hook',
	) );
}

/**
 * Misc File Download Settings Sanitization
 *
 * @since 2.5
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_misc_file_downloads( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		edd_create_protection_files( true, $input['download_method'] );
	}

	return $input;
}

add_filter( 'edd_settings_misc-file_downloads_sanitize', 'edd_settings_sanitize_misc_file_downloads' );

/**
 * Misc Accounting Settings Sanitization
 *
 * @since 2.5
 *
 * @param array $input The value inputted in the field
 *
 * @return array $input Sanitized value
 */
function edd_settings_sanitize_misc_accounting( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	return $input;
}

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 *
 * @param array $input The value inputted in the field
 *
 * @return array $input Sanitized value.
 */
function edd_settings_sanitize_taxes( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( ! isset( $_POST['tax_rates'] ) ) {
		return $input;
	}

	$tax_rates = ! empty( $_POST['tax_rates'] )
		? $_POST['tax_rates']
		: array();

	foreach ( $tax_rates as $tax_rate ) {

		$scope = isset( $tax_rate['global'] )
			? 'country'
			: 'region';

		$region = isset( $tax_rate['state'] )
			? sanitize_text_field( $tax_rate['state'] )
			: '';

		$name = '*' === $tax_rate['country']
			? ''
			: sanitize_text_field( $tax_rate['country'] );

		if ( empty( $name ) ) {
			$scope  = 'global';
		}

		$adjustment_data = array(
			'name'        => $name,
			'type'        => 'tax_rate',
			'scope'       => $scope,
			'amount_type' => 'percent',
			'amount'      => floatval( $tax_rate['rate'] ),
			'description' => $region,
		);

		if ( ( empty( $adjustment_data['name'] ) && 'global' !== $adjustment_data['scope'] ) || $adjustment_data['amount'] < 0 ) {
			continue;
		}

		$existing_adjustment = edd_get_adjustments( $adjustment_data );

		if ( ! empty( $existing_adjustment ) ) {
			$adjustment                = $existing_adjustment[0];
			$adjustment_data['status'] = sanitize_text_field( $tax_rate['status'] );

			edd_update_adjustment( $adjustment->id, $adjustment_data );
		} else {
			$adjustment_data['status'] = 'active';

			edd_add_tax_rate( $adjustment_data );
		}
	}

	return $input;
}
add_filter( 'edd_settings_taxes_sanitize', 'edd_settings_sanitize_taxes' );

/**
 * Payment Gateways Settings Sanitization
 *
 * @since 2.7
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_gateways( $input = array() ) {

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) || empty( $input['default_gateway'] ) ) {
		return $input;
	}

	// Unset the default gateway if there are no `gateways` enabled
	if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] ) {
		unset( $input['default_gateway'] );

	// Current gateway is no longer enabled, so
	} elseif ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {
		$enabled_gateways = $input['gateways'];

		reset( $enabled_gateways );

		$first_gateway = key( $enabled_gateways );

		if ( $first_gateway ) {
			$input['default_gateway'] = $first_gateway;
		}
	}

	return $input;
}
add_filter( 'edd_settings_gateways_sanitize', 'edd_settings_sanitize_gateways' );

/**
 * Sanitize text fields
 *
 * @since 1.8
 *
 * @param array $input The field value
 *
 * @return string $input Sanitized value
 */
function edd_sanitize_text_field( $input = '' ) {
	$allowed_tags = edd_get_allowed_tags();

	return trim( wp_kses( $input, $allowed_tags ) );
}

add_filter( 'edd_settings_sanitize_text', 'edd_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 2.6.11
 *
 * @param  string|array $class HTML Class Name(s)
 *
 * @return string $class
 */
function edd_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} else if ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;
}


/**
 * Sanitizes banned emails.
 *
 * @since 3.0
 */
function edd_sanitize_banned_emails( $input ) {

	$emails = '';
	if ( ! empty( $input['banned_emails'] ) ) {
		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $input['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach ( $emails as $id => $email ) {
			if ( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[ $id ] );
			}
		}
	}
	$input['banned_emails'] = $emails;

	return $input;
}
add_filter( 'edd_settings_gateways-checkout_sanitize', 'edd_sanitize_banned_emails' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @since 2.11.4 Any tabs with no registered settings are filtered out in `edd_options_page`.
 *
 * @return array $tabs
 */
function edd_get_settings_tabs() {
	return apply_filters( 'edd_settings_tabs', array(
		'general'    => __( 'General', 'easy-digital-downloads' ),
		'gateways'   => __( 'Payments', 'easy-digital-downloads' ),
		'emails'     => __( 'Emails', 'easy-digital-downloads' ),
		'marketing'  => __( 'Marketing', 'easy-digital-downloads' ),
		'styles'     => __( 'Styles', 'easy-digital-downloads' ),
		'taxes'      => __( 'Taxes', 'easy-digital-downloads' ),
		'privacy'    => __( 'Policies', 'easy-digital-downloads' ),
		'extensions' => __( 'Extensions', 'easy-digital-downloads' ),
		'licenses'   => __( 'Licenses', 'easy-digital-downloads' ),
		'misc'       => __( 'Misc', 'easy-digital-downloads' ),
	) );
}

/**
 * Retrieve settings tabs
 *
 * @since 2.5
 * @return array $section
 */
function edd_get_settings_tab_sections( $tab = false ) {
	$tabs     = array();
	$sections = edd_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = array();
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  2.5
 * @return array Array of tabs and sections
 */
function edd_get_registered_settings_sections() {
	static $sections = null;

	if ( null === $sections ) {
		$sections = array(
			'general'    => apply_filters( 'edd_settings_sections_general', array(
				'main'               => __( 'Store',      'easy-digital-downloads' ),
				'currency'           => __( 'Currency',   'easy-digital-downloads' ),
				'pages'              => __( 'Pages',      'easy-digital-downloads' ),
				'api'                => __( 'API',        'easy-digital-downloads' ),
			) ),
			'gateways'   => apply_filters( 'edd_settings_sections_gateways', array(
				'main'               => __( 'General',         'easy-digital-downloads' ),
				'checkout'           => __( 'Checkout',        'easy-digital-downloads' ),
				'refunds'            => __( 'Refunds',         'easy-digital-downloads' ),
				'accounting'         => __( 'Accounting',      'easy-digital-downloads' ),
			) ),
			'emails'     => apply_filters( 'edd_settings_sections_emails', array(
				'main'               => __( 'General',            'easy-digital-downloads' ),
				'purchase_receipts'  => __( 'Purchase Receipts',  'easy-digital-downloads' ),
				'sale_notifications' => __( 'Sale Notifications', 'easy-digital-downloads' ),
				'email_summaries'    => __( 'Summaries', 'easy-digital-downloads' ),
			) ),
			'marketing'  => apply_filters( 'edd_settings_sections_marketing', array(
				'main' => __( 'General', 'easy-digital-downloads' ),
			) ),
			'styles'     => apply_filters( 'edd_settings_sections_styles', array(
				'main'               => __( 'General', 'easy-digital-downloads' ),
				'buttons'            => __( 'Buttons', 'easy-digital-downloads' )
			) ),
			'taxes'      => apply_filters( 'edd_settings_sections_taxes', array(
				'main'               => __( 'General', 'easy-digital-downloads' ),
				'rates'              => __( 'Rates',   'easy-digital-downloads' ),
			) ),
			'privacy'    => apply_filters( 'edd_settings_section_privacy', array(
				'main'               => __( 'Privacy Policy',     'easy-digital-downloads' ),
				'site_terms'         => __( 'Terms & Agreements', 'easy-digital-downloads' ),
				'export_erase'       => __( 'Export & Erase',     'easy-digital-downloads' )
			) ),
			'extensions' => apply_filters( 'edd_settings_sections_extensions', array(
				'main'               => __( 'Main', 'easy-digital-downloads' )
			) ),
			'licenses'   => apply_filters( 'edd_settings_sections_licenses', array() ),
			'misc'       => apply_filters( 'edd_settings_sections_misc', array(
				'main'               => __( 'General',            'easy-digital-downloads' ),
				'button_text'        => __( 'Purchase Buttons',   'easy-digital-downloads' ),
				'file_downloads'     => __( 'File Downloads',     'easy-digital-downloads' ),
			) )
		);
	}

	// Filter & return
	return apply_filters( 'edd_settings_sections', $sections );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 *
 * @param bool $force Force the pages to be loaded even if not on settings
 *
 * @return array $pages_options An array of the pages
 */
function edd_get_pages( $force = false ) {

	$pages_options = array( '' => __( 'None', 'easy-digital-downloads' ) );

	if ( ( ! isset( $_GET['page'] ) || 'edd-settings' !== $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_header_callback( $args ) {
	echo apply_filters( 'edd_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @since 3.0 Updated to use `EDD_HTML_Elements`.
 *
 * @param array $args Arguments passed by the setting.
 */
function edd_checkbox_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'edd_settings[' . edd_sanitize_key( $args['id'] ) . ']';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$args['name']    = $name;
	$args['class']   = $class;
	$args['current'] = ! empty( $edd_option )
		? $edd_option
		: '';
	$args['label']   = wp_kses_post( $args['desc'] );
	$args['value']   = 1;

	$html    = '<input type="hidden" name="' . $name . '" value="-1" />';
	$html   .= '<div class="edd-check-wrapper">';
	$html   .= EDD()->html->checkbox( $args );
	$html   .= '</div>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Checkbox with description Callback
 *
 * Renders checkboxes with a description.
 *
 * @since 3.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_checkbox_description_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	// Allow a setting or filter to override what the found value is.
	if ( isset( $args['current'] ) ) {
		$edd_option = $args['current'];
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'edd_settings[' . edd_sanitize_key( $args['id'] ) . ']';
	}

	$args['name']    = $name;
	$args['class']   = edd_sanitize_html_class( $args['field_class'] );
	$args['current'] = ! empty( $edd_option ) ? $edd_option : '';
	$args['label']   = false;
	$args['value']   = 1;

	$html  = '<input type="hidden" name="' . esc_attr( $name ) . '" value="-1" />';
	$html .= '<div class="edd-check-wrapper">';
	$html .= EDD()->html->checkbox( $args );
	$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['check'] ) . '</label>';
	$html .= '</div>';
	if ( ! empty( $args['desc'] ) ) {
		$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_multicheck_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '';
	if ( ! empty( $args['options'] ) ) {
		$html .= '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';

		foreach ( $args['options'] as $key => $option ):
			if ( isset( $edd_option[ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = null;
			}
			$html .= '<div class="edd-check-wrapper">';
			$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
			$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label>';
			$html .= '</div>';
		endforeach;
		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_payment_icons_callback( $args = array() ) {

	// Start an output buffer
	ob_start();

	$edd_option = edd_get_option( $args['id'] );
	$class      = edd_sanitize_html_class( $args['field_class'] ); ?>

	<input type="hidden" name="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>]" value="-1" />
	<input type="hidden" name="edd_settings[payment_icons_order]" class="edd-order" value="<?php echo edd_get_option( 'payment_icons_order' ); ?>" />

	<?php

	// Only go if options exist
	if ( ! empty( $args['options'] ) ) :
		$class = edd_sanitize_html_class( $args['field_class'] );

		// Everything is wrapped in a sortable UL
		?><ul id="edd-payment-icons-list" class="edd-sortable-list">

		<?php foreach ( $args['options'] as $key => $option ) :
			$enabled = isset( $edd_option[ $key ] )
				? $option
				: null; ?>

			<li class="edd-check-wrapper" data-key="<?php echo edd_sanitize_key( $key ); ?>">
				<label>
					<input name="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>][<?php echo edd_sanitize_key( $key ); ?>]" id="edd_settings[<?php echo edd_sanitize_key( $args['id'] ); ?>][<?php echo edd_sanitize_key( $key ); ?>]" class="<?php echo $class; ?>" type="checkbox" value="<?php echo esc_attr( $option ); ?>" <?php echo checked( $option, $enabled, false ); ?> />

				<?php if ( edd_string_is_image_url( $key ) ) : ?>
					<span class="payment-icon-image"><img class="payment-icon" src="<?php echo esc_url( $key ); ?>" /></span>
				<?php else :

					$type = '';
					$card = strtolower( str_replace( ' ', '', $option ) );

					if ( has_filter( 'edd_accepted_payment_' . $card . '_image' ) ) {
						$image = apply_filters( 'edd_accepted_payment_' . $card . '_image', '' );

					} elseif ( has_filter( 'edd_accepted_payment_' . $key . '_image' ) ) {
						$image = apply_filters( 'edd_accepted_payment_' . $key . '_image', '' );
					} else {
						// Set the type to SVG.
						$type = 'svg';

						// Get SVG dimensions.
						$dimensions = edd_get_payment_icon_dimensions( $key );

						// Get SVG markup.
						$image = edd_get_payment_icon( array(
							'icon'    => $key,
							'width'   => $dimensions['width'],
							'height'  => $dimensions['height'],
							'title'   => $option,
							'classes' => array( 'payment-icon' )
						) );
					}

					// SVG or IMG
					if ( 'svg' === $type ) : ?>

						<span class="payment-icon-image"><?php echo $image; // Contains trusted HTML ?></span>

					<?php else : ?>

						<span class="payment-icon-image"><img class="payment-icon" src="<?php echo esc_url( $image ); ?>"/></span>

					<?php endif; ?>

				<?php endif; ?>
					<span class="payment-option-name"><?php echo $option; ?></span>
				</label>
			</li>

		<?php endforeach; ?>

		</ul>

		<p class="description" style="margin-top:16px;"><?php echo wp_kses_post( $args['desc'] ); ?></p>

	<?php endif;

	// Get the contents of the current output buffer
	$html = ob_get_clean();

	// Filter & return
	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Enforce the payment icon order (from the sortable admin area UI)
 *
 * @since 3.0
 *
 * @param array $icons
 * @return array
 */
function edd_order_accepted_payment_icons( $icons = array() ) {

	// Get the order option
	$order = edd_get_option( 'payment_icons_order', '' );

	// If order is set, enforce it
	if ( ! empty( $order ) ) {
		$order = array_flip( explode( ',', $order ) );
		$order = array_intersect_key( $order, $icons );
		$icons = array_merge( $order, $icons );
	}

	// Return ordered icons
	return $icons;
}
add_filter( 'edd_accepted_payment_icons', 'edd_order_accepted_payment_icons', 99 );

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_radio_callback( $args ) {
	$edd_options = edd_get_option( $args['id'] );

	$html = '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( $edd_options && $edd_options == $key ) {
			$checked = true;
		} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! $edd_options ) {
			$checked = true;
		}

		$html .= '<div class="edd-check-wrapper">';
		$html .= '<input name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . edd_sanitize_key( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
		$html .= '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label>';
		$html .= '</div>';
	endforeach;

	$html .= '<p class="description">' . apply_filters( 'edd_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateways_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$html  = '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="-1" />';
	$html .= '<input type="hidden" name="edd_settings[gateways_order]" class="edd-order" value="' . edd_get_option( 'gateways_order' ) . '" />';

	if ( ! empty( $args['options'] ) ) {
		$class = edd_sanitize_html_class( $args['field_class'] );
		$html .= '<ul id="edd-payment-gateways" class="edd-sortable-list">';

		foreach ( $args['options'] as $key => $option ) {
			if ( isset( $edd_option[ $key ] ) ) {
				$enabled = '1';
			} else {
				$enabled = null;
			}

			$html .= '<li class="edd-check-wrapper" data-key="' . edd_sanitize_key( $key ) . '">';
			$html .= '<label>';

			$attributes = array(
				'name'             => 'edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']',
				'id'               => 'edd_settings[' . edd_sanitize_key( $args['id'] ) . '][' . edd_sanitize_key( $key ) . ']',
				'data-gateway-key' => edd_sanitize_key( $key ),
				'checked'          => checked( '1', $enabled, false ),
				'is_setup'         => edd_is_gateway_setup( $key ),
				'disabled'         => '',
			);

			if ( ! $attributes['is_setup'] ) {
				$attributes['disabled'] = 'disabled="disabled"';
			}

			$html .= '<input name="' . $attributes['name'] . '" id="' . $attributes['id'] . '" class="' . $class . '" type="checkbox" value="1" data-gateway-key="' . $attributes['data-gateway-key'] . '" ' . $attributes['checked'] . ' ' . $attributes['disabled'] . '/>&nbsp;';
			$html .= esc_html( $option['admin_label'] );
			if ( 'manual' === $key ) {
				$html .= '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . esc_html__( 'Store Gateway', 'easy-digital-downloads' ) . '</strong>: ' . esc_html__( 'This is an internal payment gateway which can be used for manually added orders or test purchases. No money is actually processed.', 'easy-digital-downloads' ) . '"></span>';
			}

			// If a settings URL is returned, display a button to go to the settings page.
			$gateway_settings_url = edd_get_gateway_settings_url( $key );
			if ( ! empty( $gateway_settings_url ) ) {
				$html .= sprintf(
					'<a class="button edd-settings__button-settings" href="%s"><span class="screen-reader-text">%s</span></a>',
					$gateway_settings_url,
					__( 'Configure Gateway', 'easy-digital-downloads' )
				);
			}


			$html .= '</label>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		$url = edd_link_helper(
			'https://easydigitaldownloads.com/downloads/category/extensions/gateways/',
			array(
				'utm_medium'  => 'payment-settings',
				'utm_content' => 'gateways',
			)
		);

		/* translators: 1. opening link tag; do not translate; 2. closing link tag; do not translate */
		$html .= '<p class="description">' . esc_html__( 'Choose how you want to allow your customers to pay you.', 'easy-digital-downloads' ) . '<br>' . sprintf( __( 'More %1$sPayment Gateways%2$s are available.', 'easy-digital-downloads' ), '<a href="' . $url . '">', '</a>' ) . '</p>';
	}

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_gateway_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	$class = edd_sanitize_html_class( $args['field_class'] );
	if ( isset( $args['chosen'] ) ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	$html     = '<select name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '">';
	$html    .= '<option value="">' . __( 'Automatic', 'easy-digital-downloads' ) . '</option>';
	$gateways = edd_get_payment_gateways();

	foreach ( $gateways as $key => $option ) {
		$selected = isset( $edd_option )
			? selected( $key, $edd_option, false )
			: '';
		$disabled = disabled( edd_is_gateway_active( $key ), false, false );
		$html    .= '<option value="' . edd_sanitize_key( $key ) . '"' . $selected . ' ' . $disabled . '>' . esc_html( $option['admin_label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_text_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} elseif ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$placeholder = ! empty( $args['placeholder'] )
		? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"'
		: '';

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true   ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . $placeholder . ' />';
	$html    .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Email Callback
 *
 * Renders email fields.
 *
 * @since 2.8
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_email_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} elseif ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="email" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '" ' . $readonly . $disabled . ' placeholder="' . esc_attr( $placeholder ) . '" />';
	$html    .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_number_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( is_numeric( $edd_option ) ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="edd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max']  ) ? $args['max']  : 999999;
	$min  = isset( $args['min']  ) ? $args['min']  : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$readonly = ! empty( $args['readonly'] ) ? ' readonly' : '';
	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $disabled . $readonly . ' />';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_textarea_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		if ( is_array( $edd_option ) ) {
			$value = implode( "\n", maybe_unserialize( $edd_option ) );
		} else {
			$value = $edd_option;
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class       = edd_sanitize_html_class( $args['field_class'] );
	$placeholder = ! empty( $args['placeholder'] )
		? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"'
		: '';

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';

	$html  = '<textarea class="' . $class . '" cols="50" rows="5" ' . $placeholder . ' id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']"' . $readonly . '>' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_password_callback( $args ) {
	$edd_options = edd_get_option( $args['id'] );

	if ( $edd_options ) {
		$value = $edd_options;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<input type="password" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_missing_callback( $args ) {
	printf(
		wp_kses_post(
			/* translators: %s: the setting ID */
			__( 'The callback function used for the %s setting is missing.', 'easy-digital-downloads' )
		),
		'<strong>' . esc_attr( $args['id'] ) . '</strong>'
	);
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {

		// Properly set default fallback if the Select Field allows Multiple values
		if ( empty( $args['multiple'] ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		} else {
			$value = ! empty( $args['std'] ) ? $args['std'] : array();
		}

	}

	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	// Nonce
	$nonce = isset( $args['data']['nonce'] )
		? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '"'
		: '';

	// If the Select Field allows Multiple values, save as an Array
	$name_attr = 'edd_settings[' . esc_attr( $args['id'] ) . ']';
	$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

	$html = '<select ' . $nonce . ' id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

	foreach ( $args['options'] as $option => $name ) {

		if ( ! $args['multiple'] ) {
			$selected = selected( $option, $value, false );
			$html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
		} else {
			// Do an in_array() check to output selected attribute for Multiple
			$html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
		}

	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_color_select_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );
	if ( $args['chosen'] ) {
		$class .= 'edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 */
function edd_rich_editor_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		if ( ! empty( $args['allow_blank'] ) && empty( $edd_option ) ) {
			$value = '';
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	$class = edd_sanitize_html_class( $args['field_class'] );

	ob_start();

	wp_editor( stripslashes( $value ), 'edd_settings_' . esc_attr( $args['id'] ), array(
		'textarea_name' => 'edd_settings[' . esc_attr( $args['id'] ) . ']',
		'textarea_rows' => absint( $rows ),
		'editor_class'  => $class,
	) );

	if ( ! empty( $args['desc'] ) ) {
		echo '<p class="edd-rich-editor-desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	$html = ob_get_clean();

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_upload_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = edd_sanitize_html_class( $args['field_class'] );

	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html  = '<div class="edd-upload-button-wrapper">';
	$html .= '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<button data-input="#edd_settings\\[' . edd_sanitize_key( $args['id'] ) . '\\]" data-uploader-title="' . esc_html__( 'Attach File', 'easy-digital-downloads' ) . '" data-uploader-button-text="' . esc_html__( 'Attach', 'easy-digital-downloads' ) . '" class="edd_settings_upload_button button button-secondary">' . __( 'Attach File', 'easy-digital-downloads' ) . '</button>';
	$html .= '</div>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_color_callback( $args ) {
	$edd_option = edd_get_option( $args['id'] );

	if ( $edd_option ) {
		$value = $edd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html  = '<input type="text" class="' . $class . ' edd-color-picker" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_shop_states_callback( $args ) {
	$edd_option  = edd_get_option( $args['id'] );
	$states      = edd_get_shop_states();
	$class       = edd_sanitize_html_class( $args['field_class'] );
	$placeholder = isset( $args['placeholder'] )
		? $args['placeholder']
		: '';

	if ( $args['chosen'] ) {
		$class .= ' edd-select-chosen';
		if ( is_rtl() ) {
			$class .= ' chosen-rtl';
		}
	}

	if ( empty( $states ) ) {
		$class .= ' edd-no-states';
	}

	$html = '<select id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" class="' . esc_attr( trim( $class ) ) . '" data-placeholder="' . esc_html( $placeholder ) . '">';

	foreach ( $states as $option => $name ) {
		$selected = isset( $edd_option ) ? selected( $option, $edd_option, false ) : '';
		$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Outputs the "Default Rate" setting.
 *
 * @since 3.0
 *
 * @param array $args Arguments passed to the setting.
 */
function edd_tax_rate_callback( $args ) {
	echo '<input type="hidden" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" name="edd_settings[' . esc_attr( $args['id'] ) . ']" value="" />';
	echo wp_kses_post( $args['desc'] );
}

/**
 * Recapture Callback
 *
 * Renders Recapture Settings
 *
 * @since 2.10.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function edd_recapture_callback($args) {
	$client_connected = false;

	if ( class_exists( 'RecaptureEDD' ) ) {
		$client_connected = RecaptureEDD::is_ready();
	}

	ob_start();

	echo $args['desc'];

	// Output the appropriate button and label based on connection status
	if ( $client_connected ) :
		$connection_complete = get_option( 'recapture_api_key' );
		?>
		<div class="inline notice notice-<?php echo $connection_complete ? 'success' : 'warning'; ?>">
			<p>
				<?php esc_html_e( 'Recapture plugin activated.', 'easy-digital-downloads' ); ?>
				<?php
				printf(
					wp_kses_post(
						/* translators: %1$s - opening anchor tag, %2$s - closing anchor tag */
						__( '%1$sAccess your Recapture account%2$s.', 'easy-digital-downloads' )
					),
					'<a href="https://recapture.io/account" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
				?>
			</p>

			<?php if ( $connection_complete ) : ?>
				<p>
					<a id="edd-recapture-disconnect" class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=recapture-confirm-disconnect' ) ); ?>"><?php esc_html_e( 'Disconnect Recapture', 'easy-digital-downloads' ); ?></a>
				</p>
			<?php else : ?>
				<p>
					<?php
					printf(
						wp_kses_post(
							/* translators: %1$s - opening anchor tag, %2$s - closing anchor tag */
							__( '%1$sComplete your connection to Recapture%2$s', 'easy-digital-downloads' )
						),
						'<a href="' . esc_url( admin_url( 'admin.php?page=recapture' ) ) . '">',
						'</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	<?php
	else :
		?>
		<p>
			<?php
			esc_html_e( 'We recommend Recapture for recovering lost revenue by automatically sending effective, targeted emails to customers who abandon their shopping cart.', 'easy-digital-downloads' );
			echo '&nbsp;';
			printf(
				wp_kses_post(
					/* translators: %1$s - opening anchor tag, %2$s - closing anchor tag */
					__( '%1$sLearn more%2$s (Free trial available)', 'easy-digital-downloads' )
				),
				'<a href="https://recapture.io/abandoned-carts-easy-digital-downloads" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
		</p>
		<?php if ( current_user_can( 'install_plugins' ) ) : ?>
		<p>
			<button type="button" id="edd-recapture-connect" class="button button-primary"><?php esc_html_e( 'Connect with Recapture', 'easy-digital-downloads' ); ?>
			</button>
		</p>
	<?php endif; ?>

	<?php
	endif;

	echo ob_get_clean();
}

/**
 * Tax Rates Callback
 *
 * Renders tax rates table
 *
 * @since 1.6
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_tax_rates_callback( $args ) {
	$rates = edd_get_tax_rates( array(), OBJECT );

	wp_enqueue_script( 'edd-admin-tax-rates' );
	wp_enqueue_style( 'edd-admin-tax-rates' );

	wp_localize_script( 'edd-admin-tax-rates', 'eddTaxRates', array(
		'rates' => $rates,
		'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
		'i18n'  => array(
			/* translators: Tax rate country code */
			'duplicateRate' => esc_html__( 'Duplicate tax rates are not allowed. Please deactivate the existing %s tax rate before adding or activating another.', 'easy-digital-downloads' ),
			'emptyCountry'  => esc_html__( 'Please select a country.', 'easy-digital-downloads' ),
			'negativeTax'   => esc_html__( 'Please enter a tax rate greater than 0.', 'easy-digital-downloads' ),
			'emptyTax'      => esc_html__( 'Are you sure you want to add a 0% tax rate?', 'easy-digital-downloads' ),
		),
	) );

	$templates = array(
		'meta',
		'row',
		'row-empty',
		'add',
		'bulk-actions'
	);

	echo '<p>' . $args['desc'] . '</p>';

	echo '<div id="edd-admin-tax-rates"></div>';

	foreach ( $templates as $tmpl ) {
?>

<script type="text/html" id="tmpl-edd-admin-tax-rates-table-<?php echo esc_attr( $tmpl ); ?>">
	<?php require_once EDD_PLUGIN_DIR . 'includes/admin/views/tmpl-tax-rates-table-' . $tmpl . '.php'; ?>
</script>

<?php
	}

}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_descriptive_text_callback( $args ) {
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing.
 *
 * @since 1.5
 * @since 3.1.1 Updated to use the extension licenses class.
 * @param array $args Arguments passed by the setting.
 * @return void
 */
if ( ! function_exists( 'edd_license_key_callback' ) ) {
	function edd_license_key_callback( $args ) {
		$settings_field = new EDD\Licensing\Settings( $args );
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 *
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function edd_hook_callback( $args ) {
	// Since our settings are hook based, just be sure the user can manage shop settings before firing the setting hook.
	if ( ! current_user_can( 'manage_shop_settings') ) {
		return;
	}

	do_action( 'edd_' . $args['id'], $args );
}

/**
 * Set manage_shop_settings as the cap required to save EDD settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function edd_set_settings_cap() {
	return 'manage_shop_settings';
}
add_filter( 'option_page_capability_edd_settings', 'edd_set_settings_cap' );

/**
 * Maybe attach a tooltip to a setting
 *
 * @since 1.9
 * @param string $html
 * @param type $args
 * @return string
 */
function edd_add_setting_tooltip( $html = '', $args = array() ) {

	// Tooltip has title & description
	if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
		$tooltip   = '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . esc_html( $args['tooltip_title'] ) . '</strong>: ' . esc_html( $args['tooltip_desc'] ) . '"></span>';
		$has_p_tag = strstr( $html, '</p>'     );
		$has_label = strstr( $html, '</label>' );

		// Insert tooltip at end of paragraph
		if ( false !== $has_p_tag ) {
			$html = str_replace( '</p>', $tooltip . '</p>', $html );

		// Insert tooltip at end of label
		} elseif ( false !== $has_label ) {
			$html = str_replace( '</label>', '</label>' . $tooltip, $html );

		// Append tooltip to end of HTML
		} else {
			$html .= $tooltip;
		}
	}

	return $html;
}
add_filter( 'edd_after_setting_output', 'edd_add_setting_tooltip', 10, 2 );

/**
 * Filters the edd_get_option call for test_mode.
 *
 * This allows us to ensure that calls directly to edd_get_option respect the constant
 * in addition to the edd_is_test_mode() function call and included filter.
 *
 * @since 3.1
 *
 * @param bool   $value   If test_mode is enabled in the settings.
 * @param string $key     The key of the setting, should be test_mode.
 * @param bool   $default The default setting, which is 'false' for test_mode.
 */
function edd_filter_test_mode_option( $value, $key, $default ) {
	if ( edd_is_test_mode_forced() ) {
		$value = true;
	}

	return $value;
}
add_filter( 'edd_get_option_test_mode', 'edd_filter_test_mode_option', 10, 3 );

/**
 * Determine if test mode is being forced to true.
 *
 * Using the EDD_TEST_MODE and the edd_is_test_mode filter, determine if the value of true
 * is being forced for test_mode so we can properly alter the setting for it.
 *
 * @since 3.1
 *
 * @return bool If test_mode is being forced or not.
 */
function edd_is_test_mode_forced() {
	if ( defined( 'EDD_TEST_MODE' ) && true === EDD_TEST_MODE ) {
		return true;
	}

	if ( false !== has_filter( 'edd_is_test_mode', '__return_true' ) ) {
		return true;
	}

	return false;
}

/**
 * Checks for an incorrect setting for the privacy policy.
 * Required in updating from EDD 2.9.2 to 2.9.3.
 */
add_filter( 'edd_get_option_show_privacy_policy_on_checkout', function( $value ) {
	if ( ! empty( $value ) ) {
		return $value;
	}
	$fix_show_privacy_policy_setting = edd_get_option( 'show_agree_to_privacy_policy_on_checkout', false );
	if ( ! empty( $fix_show_privacy_policy_setting ) ) {
		edd_update_option( 'show_privacy_policy_on_checkout', $fix_show_privacy_policy_setting );
		edd_delete_option( 'show_agree_to_privacy_policy_on_checkout' );

		return $fix_show_privacy_policy_setting;
	}

	return $value;
}, 10, 3 );
