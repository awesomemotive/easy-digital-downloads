<?php
/**
 * Misc Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Misc Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Is Test Mode
 *
 * @access      public
 * @since       1.0
 * @return      boolean
*/

function edd_is_test_mode() {
	global $edd_options;
	if( !isset( $edd_options['test_mode'] ) || is_null( $edd_options['test_mode'] ) )
		$ret = false;
	else
		$ret = true;

	return (bool) apply_filters( 'edd_is_test_mode', $ret );
}


/**
 * No Guest Checkout
 *
 * @access      public
 * @since       1.0
 * @return      boolean
*/

function edd_no_guest_checkout() {
	global $edd_options;
	if( isset( $edd_options['logged_in_only'] ) )
		$ret = true;
	else
		$ret = false;

	return (bool) apply_filters( 'edd_no_guest_checkout', $ret );
}


/**
 * Logged in Only
 *
 * @access      public
 * @since       1.0
 * @return      boolean
*/

function edd_logged_in_only() {
	global $edd_options;
	if( isset( $edd_options['logged_in_only'] ) )
		$ret = true;
	else
		$ret = false;

	return (bool) apply_filters( 'edd_logged_in_only', $ret );
}


/**
 * Disable Redownload
 *
 * @access      public
 * @since       1.0.8.2
 * @return      boolean
*/

function edd_no_redownload() {
	global $edd_options;
	if( isset( $edd_options['disable_redownload'] ) )
		return true;
	return (bool) apply_filters( 'edd_no_redownload', false );
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access
 * the downloads menu. Currently not changeable,
 * but here for a future update.
 *
 * @access      public
 * @since       1.0
 * @return      string
*/

function edd_get_menu_access_level() {
	return apply_filters( 'edd_menu_access_level', 'manage_options' );
}


/**
 * Is Odd
 *
 * Checks wether an integer is odd.
 *
 * @access      public
 * @since       1.0
 * @return      boolean
*/

function edd_is_odd( $int ) {
	return (bool) ( $int & 1 );
}


/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @access      public
 * @since       1.0
 * @return      string
*/

function edd_get_file_extension( $str ) {
   $parts = explode( '.', $str );
   return end( $parts );
}


function edd_string_is_image_url( $str ) {
	$ext = edd_get_file_extension( $str );

	switch( strtolower( $ext ) ) {
		case 'jpg';
			$return = true;
			break;
		case 'png';
			$return = true;
			break;
		case 'gif';
			$return = true;
			break;
		default:
			$return = false;
		break;
	}

	return (bool) apply_filters( 'edd_string_is_image', $return, $str );
}



/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @access      public
 * @since       1.0.8.2
 * @return      string
*/

function edd_get_ip() {
	if( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
	  $ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
	  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
	  $ip = $_SERVER['REMOTE_ADDR'];
	}
	return apply_filters( 'edd_get_ip', $ip );
}


/**
 * Get Currencies
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_get_currencies() {
	$currencies = array(
		'USD' => __('US Dollars (&#36;)', 'edd'),
		'EUR' => __('Euros (&euro;)', 'edd'),
		'GBP' => __('Pounds Sterling (&pound;)', 'edd'),
		'AUD' => __('Australian Dollars (&#36;)', 'edd'),
		'BRL' => __('Brazilian Real (&#36;)', 'edd'),
		'CAD' => __('Canadian Dollars (&#36;)', 'edd'),
		'CZK' => __('Czech Koruna', 'edd'),
		'DKK' => __('Danish Krone', 'edd'),
		'HKD' => __('Hong Kong Dollar (&#36;)', 'edd'),
		'HUF' => __('Hungarian Forint', 'edd'),
		'ILS' => __('Israeli Shekel', 'edd'),
		'JPY' => __('Japanese Yen (&yen;)', 'edd'),
		'MYR' => __('Malaysian Ringgits', 'edd'),
		'MXN' => __('Mexican Peso (&#36;)', 'edd'),
		'NZD' => __('New Zealand Dollar (&#36;)', 'edd'),
		'NOK' => __('Norwegian Krone', 'edd'),
		'PHP' => __('Philippine Pesos', 'edd'),
		'PLN' => __('Polish Zloty', 'edd'),
		'SGD' => __('Singapore Dollar (&#36;)', 'edd'),
		'SEK' => __('Swedish Krona', 'edd'),
		'CHF' => __('Swiss Franc', 'edd'),
		'TWD' => __('Taiwan New Dollars', 'edd'),
		'THB' => __('Thai Baht', 'edd'),
		'INR' => __('Indian Rupee', 'edd'),
		'TRY' => __('Turkish Lira', 'edd'),
		'RIAL' => __('Iranian Rial', 'edd')
	);

	return apply_filters( 'edd_currencies', $currencies );
}


/**
 * Get Country List
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_get_country_list() {
	$countries =array(
		'US' => 'United States',
		'CA' => 'Canada',
		'GB' => 'United Kingdom',
		'AD' => 'Andorra',
		'AE' => 'United Arab Emirates',
		'AF' => 'Afghanistan',
		'AG' => 'Antigua and Barbuda',
		'AI' => 'Anguilla',
		'AL' => 'Albania',
		'AM' => 'Armenia',
		'AN' => 'Netherlands Antilles',
		'AO' => 'Angola',
		'AQ' => 'Antarctica',
		'AR' => 'Argentina',
		'AS' => 'American Samoa',
		'AT' => 'Austria',
		'AU' => 'Australia',
		'AW' => 'Aruba',
		'AZ' => 'Azerbaijan',
		'BA' => 'Bosnia and Herzegovina',
		'BB' => 'Barbados',
		'BD' => 'Bangladesh',
		'BE' => 'Belgium',
		'BF' => 'Burkina Faso',
		'BG' => 'Bulgaria',
		'BH' => 'Bahrain',
		'BI' => 'Burundi',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BN' => 'Brunei Darrussalam',
		'BO' => 'Bolivia',
		'BR' => 'Brazil',
		'BS' => 'Bahamas',
		'BT' => 'Bhutan',
		'BV' => 'Bouvet Island',
		'BW' => 'Botswana',
		'BY' => 'Belarus',
		'BZ' => 'Belize',
		'CC' => 'Cocos Islands',
		'CD' => 'Congo, Democratic People\'s Republic',
		'CF' => 'Central African Republic',
		'CG' => 'Congo, Republic of',
		'CH' => 'Switzerland',
		'CI' => 'Cote d\'Ivoire',
		'CK' => 'Cook Islands',
		'CL' => 'Chile',
		'CM' => 'Cameroon',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CR' => 'Costa Rica',
		'CU' => 'Cuba',
		'CV' => 'Cap Verde',
		'CX' => 'Christmas Island',
		'CY' => 'Cyprus Island',
		'CZ' => 'Czech Republic',
		'DE' => 'Germany',
		'DJ' => 'Djibouti',
		'DK' => 'Denmark',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'DZ' => 'Algeria',
		'EC' => 'Ecuador',
		'EE' => 'Estonia',
		'EG' => 'Egypt',
		'EH' => 'Western Sahara',
		'ER' => 'Eritrea',
		'ES' => 'Spain',
		'ET' => 'Ethiopia',
		'FI' => 'Finland',
		'FJ' => 'Fiji',
		'FK' => 'Falkland Islands',
		'FM' => 'Micronesia',
		'FO' => 'Faroe Islands',
		'FR' => 'France',
		'GA' => 'Gabon',
		'GD' => 'Grenada',
		'GE' => 'Georgia',
		'GF' => 'French Guiana',
		'GG' => 'Guernsey',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GM' => 'Gambia',
		'GN' => 'Guinea',
		'GP' => 'Guadeloupe',
		'GQ' => 'Equatorial Guinea',
		'GR' => 'Greece',
		'GS' => 'South Georgia',
		'GT' => 'Guatemala',
		'GU' => 'Guam',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HK' => 'Hong Kong',
		'HM' => 'Heard and McDonald Islands',
		'HN' => 'Honduras',
		'HR' => 'Croatia/Hrvatska',
		'HT' => 'Haiti',
		'HU' => 'Hungary',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IM' => 'Isle of Man',
		'IN' => 'India',
		'IO' => 'British Indian Ocean Territory',
		'IQ' => 'Iraq',
		'IR' => 'Iran',
		'IS' => 'Iceland',
		'IT' => 'Italy',
		'JE' => 'Jersey',
		'JM' => 'Jamaica',
		'JO' => 'Jordan',
		'JP' => 'Japan',
		'KE' => 'Kenya',
		'KG' => 'Kyrgyzstan',
		'KH' => 'Cambodia',
		'KI' => 'Kiribati',
		'KM' => 'Comoros',
		'KN' => 'Saint Kitts and Nevis',
		'KP' => 'South Korea',
		'KR' => 'North Korea',
		'KW' => 'Kuwait',
		'KY' => 'Cayman Islands',
		'KZ' => 'Kazakhstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LB' => 'Lebanon',
		'LC' => 'Saint Lucia',
		'LI' => 'Liechtenstein',
		'LK' => 'Sri Lanka',
		'LR' => 'Liberia',
		'LS' => 'Lesotho',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourgh',
		'LV' => 'Latvia',
		'LY' => 'Libyan Arab Jamahiriya',
		'MA' => 'Morocco',
		'MC' => 'Monaco',
		'ME' => 'Montenegro',
		'MD' => 'Moldova, Republic of',
		'MG' => 'Madagascar',
		'MH' => 'Marshall Islands',
		'MK' => 'Macedonia',
		'ML' => 'Mali',
		'MM' => 'Myanmar',
		'MN' => 'Mongolia',
		'MO' => 'Macau',
		'MP' => 'Northern Mariana Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MS' => 'Montserrat',
		'MT' => 'Malta',
		'MU' => 'Mauritius',
		'Mv' => 'Maldives',
		'MW' => 'malawi',
		'MX' => 'Mexico',
		'MY' => 'Malaysia',
		'MZ' => 'Mozambique',
		'NA' => 'Namibia',
		'NC' => 'New Caledonia',
		'NE' => 'Niger',
		'NF' => 'Norfolk Island',
		'NG' => 'Nigeria',
		'NI' => 'Nicaragua',
		'NL' => 'Netherlands',
		'NO' => 'Norway',
		'NP' => 'Nepal',
		'NR' => 'Nauru',
		'NU' => 'Niue',
		'NZ' => 'New Zealand',
		'OM' => 'Oman',
		'PA' => 'Panama',
		'PE' => 'Peru',
		'PF' => 'French Polynesia',
		'PG' => 'papua New Guinea',
		'PH' => 'Phillipines',
		'PK' => 'Pakistan',
		'PL' => 'Poland',
		'PM' => 'St. Pierre and Miquelon',
		'PN' => 'Pitcairn Island',
		'PR' => 'Puerto Rico',
		'PS' => 'Palestinian Territories',
		'PT' => 'Portugal',
		'PW' => 'Palau',
		'PY' => 'Paraguay',
		'QA' => 'Qatar',
		'RE' => 'Reunion Island',
		'RO' => 'Romania',
		'RS' => 'Serbia',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'SA' => 'Saudi Arabia',
		'SB' => 'Solomon Islands',
		'SC' => 'Seychelles',
		'SD' => 'Sudan',
		'SE' => 'Sweden',
		'SG' => 'Singapore',
		'SH' => 'St. Helena',
		'SI' => 'Slovenia',
		'SJ' => 'Svalbard and Jan Mayen Islands',
		'SK' => 'Slovak Republic',
		'SL' => 'Sierra Leone',
		'SM' => 'San Marino',
		'SN' => 'Senegal',
		'SO' => 'Somalia',
		'SR' => 'Suriname',
		'ST' => 'Sao Tome and Principe',
		'SV' => 'El Salvador',
		'SY' => 'Syrian Arab Republic',
		'SZ' => 'Swaziland',
		'TC' => 'Turks and Caicos Islands',
		'TD' => 'Chad',
		'TF' => 'French Southern Territories',
		'TG' => 'Togo',
		'TH' => 'Thailand',
		'TJ' => 'Tajikistan',
		'TK' => 'Tokelau',
		'TM' => 'Turkmenistan',
		'TN' => 'Tunisia',
		'TO' => 'Tonga',
		'TP' => 'East Timor',
		'TR' => 'Turkey',
		'TT' => 'Trinidad and Tobago',
		'TV' => 'Tuvalu',
		'TW' => 'Taiwan',
		'TZ' => 'Tanzania',
		'UA' => 'Ukraine',
		'UG' => 'Uganda',
		'UM' => 'US Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VA' => 'Holy See (City Vatican State)',
		'VC' => 'Saint Vincent and the Grenadines',
		'VE' => 'Venezuela',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (USA)',
		'VN' => 'Vietnam',
		'VU' => 'Vanuatu',
		'WF' => 'Wallis and Futuna Islands',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'YT' => 'Mayotte',
		'YU' => 'Yugoslavia',
		'ZA' => 'South Africa',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	return apply_filters( 'edd_countries', $countries );
}


/**
 * Get States List
 *
 * @access      public
 * @since       1.2
 * @return      array
*/

function edd_get_states_list() {
	$states =array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraksa',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'AS' => 'American Samoa',
		'CZ' => 'Canal Zone',
		'CM' => 'Commonwealth of the Northern Mariana Islands',
		'FM' => 'Federated States of Micronesia',
		'GU' => 'Guam',
		'MH' => 'Marshall Islands',
		'MP' => 'Northern Mariana Islands',
		'PW' => 'Palau',
		'PI' => 'Philippine Islands',
		'PR' => 'Puerto Rico',
		'TT' => 'Trust Territory of the Pacific Islands',
		'VI' => 'Virgin Islands',
		'AA' => 'Armed Forces - Americas',
		'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
		'AP' => 'Armed Forces - Pacific'
	);

	return apply_filters( 'edd_us_states', $states );
}


/**
 * Get Provinces List
 *
 * @access      public
 * @since       1.2
 * @return      array
*/

function edd_get_provinces_list() {
	$provinces = array(
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NL' => 'Newfoundland and Labrador',
		'NS' => 'Nova Scotia',
		'NT' => 'Northwest Territories',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon'
	);

	return apply_filters( 'edd_canada_provinces', $provinces );
}


/**
 * Month Num To Name
 *
 * Takes a month number and returns the
 * name three letter name of it.
 *
 * @access      public
 * @since       1.0
 * @return      string
*/

function edd_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date( "M", $timestamp );
}


/**
 * Get PHP Arg Seaparator Ouput
 *
 * @access      public
 * @since       1.0.8.3
 * @return      string
*/

function edd_get_php_arg_separator_output() {
	return ini_get('arg_separator.output');
}


/**
 * Get the current page URL
 *
 * @access      public
 * @since       1.3
 * @return      string
*/

function edd_get_current_page_url() {
	global $post;

	if( is_singular() ):
		$pageURL = get_permalink( $post->ID );
	else :
		$pageURL = 'http';
		if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) $pageURL .= "s";
		$pageURL .= "://";
		if( $_SERVER["SERVER_PORT"] != "80" ) $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	endif;

	return apply_filters( 'edd_get_current_page_url', $pageURL );
}



/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook edd_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @package Easy Digital Downloads
 * @subpackage  Misc Functions
 * @since 1.3.1
 * @access private
 *
 * @uses do_action() Calls 'edd_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'edd_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 */
function _edd_deprecated_function( $function, $version, $replacement = null ) {

	do_action( 'edd_deprecated_function_run', $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' ) ? true : false;


	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'edd_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null($replacement) )
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s! Use %3$s instead.', 'edd' ), $function, $version, $replacement ) );
		else
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since Easy Digital Downloads version %2$s with no alternative available.', 'edd'), $function, $version ) );
	}
}




/**
 * PressTrends plugin API
 *
 * @access      public
 * @since       1.3.2
 * @return      void
*/

function edd_presstrends() {

	global $edd_options;

	if( ! isset( $edd_options['presstrends'] ) )
		return;

	// PressTrends Account API Key
	$api_key = '5s8akq2i874z40j69yceyb54qodzg1ux3wtf';
	$auth    = 'xz27f52esm948ogb5xah9bpk4x54usai8';

	// Start of Metrics
	global $wpdb;
	$data = get_transient( 'presstrends_cache_data' );
	if ( !$data || $data == '' ) {
		$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
		$url      = $api_base . $auth . '/api/' . $api_key . '/';

		$count_posts    = wp_count_posts();
		$count_pages    = wp_count_posts( 'page' );
		$comments_count = wp_count_comments();

		// wp_get_theme was introduced in 3.4, for compatibility with older versions, let's do a workaround for now.
		if ( function_exists( 'wp_get_theme' ) ) {
			$theme_data = wp_get_theme();
			$theme_name = urlencode( $theme_data->Name );
		} else {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
			$theme_name = $theme_data['Name'];
		}

		$plugin_name = '&';
		foreach ( get_plugins() as $plugin_info ) {
			$plugin_name .= $plugin_info['Name'] . '&';
		}
		// CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
		$plugin_data         = get_plugin_data( EDD_PLUGIN_FILE );
		$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
		$data                = array(
			'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
			'posts'           => $count_posts->publish,
			'pages'           => $count_pages->publish,
			'comments'        => $comments_count->total_comments,
			'approved'        => $comments_count->approved,
			'spam'            => $comments_count->spam,
			'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
			'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
			'theme_version'   => $plugin_data['Version'],
			'theme_name'      => $theme_name,
			'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
			'plugins'         => count( get_option( 'active_plugins' ) ),
			'plugin'          => urlencode( $plugin_name ),
			'wpversion'       => get_bloginfo( 'version' ),
		);

		foreach ( $data as $k => $v ) {
			$url .= $k . '/' . $v . '/';
		}
		wp_remote_get( $url );
		set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
	}
}
add_action( 'admin_init', 'edd_presstrends' );

/**
 * Checks whether function is disabled.
 *
 * @access public
 * @since  1.3.5
 *
 * @param  string $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function edd_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}