<?php
/**
 * Country Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get Shop Base Country
 *
 * @since 1.6
 * @return string $country The two letter country code for the shop's base country
 */
function edd_get_shop_country() {
	$country = edd_get_option( 'base_country', 'US' );

	return apply_filters( 'edd_shop_country', $country );
}

/**
 * Get Shop Base State
 *
 * @since 1.6
 * @return string $state The shop's base state name
 */
function edd_get_shop_state() {
	$state = edd_get_option( 'base_state', false );

	return apply_filters( 'edd_shop_state', $state );
}

/**
 * Get Shop States
 *
 * @since 1.6
 *
 * @param string $country
 * @return array A list of states for the selected country
 */
function edd_get_shop_states( $country = null ) {
	if( empty( $country ) )
		$country = edd_get_shop_country();

	switch( $country ) :

		case 'US' :
			$states = edd_get_states_list();
			break;
		case 'CA' :
			$states = edd_get_provinces_list();
			break;
		case 'AU' :
			$states = edd_get_australian_states_list();
			break;
		case 'BD' :
			$states = edd_get_bangladeshi_states_list();
			break;
		case 'BG' :
			$states = edd_get_bulgarian_states_list();
			break;
		case 'BR' :
			$states = edd_get_brazil_states_list();
			break;
		case 'CN' :
			$states = edd_get_chinese_states_list();
			break;
		case 'HK' :
			$states = edd_get_hong_kong_states_list();
			break;
		case 'HU' :
			$states = edd_get_hungary_states_list();
			break;
		case 'ID' :
			$states = edd_get_indonesian_states_list();
			break;
		case 'IN' :
			$states = edd_get_indian_states_list();
			break;
		case 'IR' :
			$states = edd_get_iranian_states_list();
			break;
		case 'IT' :
			$states = edd_get_italian_states_list();
			break;
		case 'JP' :
			$states = edd_get_japanese_states_list();
			break;
		case 'MX' :
			$states = edd_get_mexican_states_list();
			break;
		case 'MY' :
			$states = edd_get_malaysian_states_list();
			break;
		case 'NP' :
			$states = edd_get_nepalese_states_list();
			break;
		case 'NZ' :
			$states = edd_get_new_zealand_states_list();
			break;
		case 'PE' :
			$states = edd_get_peruvian_states_list();
			break;
		case 'TH' :
			$states = edd_get_thailand_states_list();
			break;
		case 'TR' :
			$states = edd_get_turkey_states_list();
			break;
		case 'ZA' :
			$states = edd_get_south_african_states_list();
			break;
		case 'ES' :
			$states = edd_get_spain_states_list();
			break;
		default :
			$states = array();
			break;

	endswitch;

	return apply_filters( 'edd_shop_states', $states, $country );
}


/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries
 */
function edd_get_country_list() {
	$countries = array(
		''   => '',
		'US' => __( 'United States', 'easy-digital-downloads' ),
		'CA' => __( 'Canada', 'easy-digital-downloads' ),
		'GB' => __( 'United Kingdom', 'easy-digital-downloads' ),
		'AF' => __( 'Afghanistan', 'easy-digital-downloads' ),
		'AX' => __( '&#197;land Islands', 'easy-digital-downloads' ),
		'AL' => __( 'Albania', 'easy-digital-downloads' ),
		'DZ' => __( 'Algeria', 'easy-digital-downloads' ),
		'AS' => __( 'American Samoa', 'easy-digital-downloads' ),
		'AD' => __( 'Andorra', 'easy-digital-downloads' ),
		'AO' => __( 'Angola', 'easy-digital-downloads' ),
		'AI' => __( 'Anguilla', 'easy-digital-downloads' ),
		'AQ' => __( 'Antarctica', 'easy-digital-downloads' ),
		'AG' => __( 'Antigua and Barbuda', 'easy-digital-downloads' ),
		'AR' => __( 'Argentina', 'easy-digital-downloads' ),
		'AM' => __( 'Armenia', 'easy-digital-downloads' ),
		'AW' => __( 'Aruba', 'easy-digital-downloads' ),
		'AU' => __( 'Australia', 'easy-digital-downloads' ),
		'AT' => __( 'Austria', 'easy-digital-downloads' ),
		'AZ' => __( 'Azerbaijan', 'easy-digital-downloads' ),
		'BS' => __( 'Bahamas', 'easy-digital-downloads' ),
		'BH' => __( 'Bahrain', 'easy-digital-downloads' ),
		'BD' => __( 'Bangladesh', 'easy-digital-downloads' ),
		'BB' => __( 'Barbados', 'easy-digital-downloads' ),
		'BY' => __( 'Belarus', 'easy-digital-downloads' ),
		'BE' => __( 'Belgium', 'easy-digital-downloads' ),
		'BZ' => __( 'Belize', 'easy-digital-downloads' ),
		'BJ' => __( 'Benin', 'easy-digital-downloads' ),
		'BM' => __( 'Bermuda', 'easy-digital-downloads' ),
		'BT' => __( 'Bhutan', 'easy-digital-downloads' ),
		'BO' => __( 'Bolivia', 'easy-digital-downloads' ),
		'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'easy-digital-downloads' ),
		'BA' => __( 'Bosnia and Herzegovina', 'easy-digital-downloads' ),
		'BW' => __( 'Botswana', 'easy-digital-downloads' ),
		'BV' => __( 'Bouvet Island', 'easy-digital-downloads' ),
		'BR' => __( 'Brazil', 'easy-digital-downloads' ),
		'IO' => __( 'British Indian Ocean Territory', 'easy-digital-downloads' ),
		'BN' => __( 'Brunei Darrussalam', 'easy-digital-downloads' ),
		'BG' => __( 'Bulgaria', 'easy-digital-downloads' ),
		'BF' => __( 'Burkina Faso', 'easy-digital-downloads' ),
		'BI' => __( 'Burundi', 'easy-digital-downloads' ),
		'KH' => __( 'Cambodia', 'easy-digital-downloads' ),
		'CM' => __( 'Cameroon', 'easy-digital-downloads' ),
		'CV' => __( 'Cape Verde', 'easy-digital-downloads' ),
		'KY' => __( 'Cayman Islands', 'easy-digital-downloads' ),
		'CF' => __( 'Central African Republic', 'easy-digital-downloads' ),
		'TD' => __( 'Chad', 'easy-digital-downloads' ),
		'CL' => __( 'Chile', 'easy-digital-downloads' ),
		'CN' => __( 'China', 'easy-digital-downloads' ),
		'CX' => __( 'Christmas Island', 'easy-digital-downloads' ),
		'CC' => __( 'Cocos Islands', 'easy-digital-downloads' ),
		'CO' => __( 'Colombia', 'easy-digital-downloads' ),
		'KM' => __( 'Comoros', 'easy-digital-downloads' ),
		'CD' => __( 'Congo, Democratic People\'s Republic', 'easy-digital-downloads' ),
		'CG' => __( 'Congo, Republic of', 'easy-digital-downloads' ),
		'CK' => __( 'Cook Islands', 'easy-digital-downloads' ),
		'CR' => __( 'Costa Rica', 'easy-digital-downloads' ),
		'CI' => __( 'Cote d\'Ivoire', 'easy-digital-downloads' ),
		'HR' => __( 'Croatia/Hrvatska', 'easy-digital-downloads' ),
		'CU' => __( 'Cuba', 'easy-digital-downloads' ),
		'CW' => __( 'Cura&Ccedil;ao', 'easy-digital-downloads' ),
		'CY' => __( 'Cyprus', 'easy-digital-downloads' ),
		'CZ' => __( 'Czech Republic', 'easy-digital-downloads' ),
		'DK' => __( 'Denmark', 'easy-digital-downloads' ),
		'DJ' => __( 'Djibouti', 'easy-digital-downloads' ),
		'DM' => __( 'Dominica', 'easy-digital-downloads' ),
		'DO' => __( 'Dominican Republic', 'easy-digital-downloads' ),
		'TP' => __( 'East Timor', 'easy-digital-downloads' ),
		'EC' => __( 'Ecuador', 'easy-digital-downloads' ),
		'EG' => __( 'Egypt', 'easy-digital-downloads' ),
		'GQ' => __( 'Equatorial Guinea', 'easy-digital-downloads' ),
		'SV' => __( 'El Salvador', 'easy-digital-downloads' ),
		'ER' => __( 'Eritrea', 'easy-digital-downloads' ),
		'EE' => __( 'Estonia', 'easy-digital-downloads' ),
		'ET' => __( 'Ethiopia', 'easy-digital-downloads' ),
		'FK' => __( 'Falkland Islands', 'easy-digital-downloads' ),
		'FO' => __( 'Faroe Islands', 'easy-digital-downloads' ),
		'FJ' => __( 'Fiji', 'easy-digital-downloads' ),
		'FI' => __( 'Finland', 'easy-digital-downloads' ),
		'FR' => __( 'France', 'easy-digital-downloads' ),
		'GF' => __( 'French Guiana', 'easy-digital-downloads' ),
		'PF' => __( 'French Polynesia', 'easy-digital-downloads' ),
		'TF' => __( 'French Southern Territories', 'easy-digital-downloads' ),
		'GA' => __( 'Gabon', 'easy-digital-downloads' ),
		'GM' => __( 'Gambia', 'easy-digital-downloads' ),
		'GE' => __( 'Georgia', 'easy-digital-downloads' ),
		'DE' => __( 'Germany', 'easy-digital-downloads' ),
		'GR' => __( 'Greece', 'easy-digital-downloads' ),
		'GH' => __( 'Ghana', 'easy-digital-downloads' ),
		'GI' => __( 'Gibraltar', 'easy-digital-downloads' ),
		'GL' => __( 'Greenland', 'easy-digital-downloads' ),
		'GD' => __( 'Grenada', 'easy-digital-downloads' ),
		'GP' => __( 'Guadeloupe', 'easy-digital-downloads' ),
		'GU' => __( 'Guam', 'easy-digital-downloads' ),
		'GT' => __( 'Guatemala', 'easy-digital-downloads' ),
		'GG' => __( 'Guernsey', 'easy-digital-downloads' ),
		'GN' => __( 'Guinea', 'easy-digital-downloads' ),
		'GW' => __( 'Guinea-Bissau', 'easy-digital-downloads' ),
		'GY' => __( 'Guyana', 'easy-digital-downloads' ),
		'HT' => __( 'Haiti', 'easy-digital-downloads' ),
		'HM' => __( 'Heard and McDonald Islands', 'easy-digital-downloads' ),
		'VA' => __( 'Holy See (City Vatican State)', 'easy-digital-downloads' ),
		'HN' => __( 'Honduras', 'easy-digital-downloads' ),
		'HK' => __( 'Hong Kong', 'easy-digital-downloads' ),
		'HU' => __( 'Hungary', 'easy-digital-downloads' ),
		'IS' => __( 'Iceland', 'easy-digital-downloads' ),
		'IN' => __( 'India', 'easy-digital-downloads' ),
		'ID' => __( 'Indonesia', 'easy-digital-downloads' ),
		'IR' => __( 'Iran', 'easy-digital-downloads' ),
		'IQ' => __( 'Iraq', 'easy-digital-downloads' ),
		'IE' => __( 'Ireland', 'easy-digital-downloads' ),
		'IM' => __( 'Isle of Man', 'easy-digital-downloads' ),
		'IL' => __( 'Israel', 'easy-digital-downloads' ),
		'IT' => __( 'Italy', 'easy-digital-downloads' ),
		'JM' => __( 'Jamaica', 'easy-digital-downloads' ),
		'JP' => __( 'Japan', 'easy-digital-downloads' ),
		'JE' => __( 'Jersey', 'easy-digital-downloads' ),
		'JO' => __( 'Jordan', 'easy-digital-downloads' ),
		'KZ' => __( 'Kazakhstan', 'easy-digital-downloads' ),
		'KE' => __( 'Kenya', 'easy-digital-downloads' ),
		'KI' => __( 'Kiribati', 'easy-digital-downloads' ),
		'KW' => __( 'Kuwait', 'easy-digital-downloads' ),
		'KG' => __( 'Kyrgyzstan', 'easy-digital-downloads' ),
		'LA' => __( 'Lao People\'s Democratic Republic', 'easy-digital-downloads' ),
		'LV' => __( 'Latvia', 'easy-digital-downloads' ),
		'LB' => __( 'Lebanon', 'easy-digital-downloads' ),
		'LS' => __( 'Lesotho', 'easy-digital-downloads' ),
		'LR' => __( 'Liberia', 'easy-digital-downloads' ),
		'LY' => __( 'Libyan Arab Jamahiriya', 'easy-digital-downloads' ),
		'LI' => __( 'Liechtenstein', 'easy-digital-downloads' ),
		'LT' => __( 'Lithuania', 'easy-digital-downloads' ),
		'LU' => __( 'Luxembourg', 'easy-digital-downloads' ),
		'MO' => __( 'Macau', 'easy-digital-downloads' ),
		'MK' => __( 'Macedonia', 'easy-digital-downloads' ),
		'MG' => __( 'Madagascar', 'easy-digital-downloads' ),
		'MW' => __( 'Malawi', 'easy-digital-downloads' ),
		'MY' => __( 'Malaysia', 'easy-digital-downloads' ),
		'MV' => __( 'Maldives', 'easy-digital-downloads' ),
		'ML' => __( 'Mali', 'easy-digital-downloads' ),
		'MT' => __( 'Malta', 'easy-digital-downloads' ),
		'MH' => __( 'Marshall Islands', 'easy-digital-downloads' ),
		'MQ' => __( 'Martinique', 'easy-digital-downloads' ),
		'MR' => __( 'Mauritania', 'easy-digital-downloads' ),
		'MU' => __( 'Mauritius', 'easy-digital-downloads' ),
		'YT' => __( 'Mayotte', 'easy-digital-downloads' ),
		'MX' => __( 'Mexico', 'easy-digital-downloads' ),
		'FM' => __( 'Micronesia', 'easy-digital-downloads' ),
		'MD' => __( 'Moldova, Republic of', 'easy-digital-downloads' ),
		'MC' => __( 'Monaco', 'easy-digital-downloads' ),
		'MN' => __( 'Mongolia', 'easy-digital-downloads' ),
		'ME' => __( 'Montenegro', 'easy-digital-downloads' ),
		'MS' => __( 'Montserrat', 'easy-digital-downloads' ),
		'MA' => __( 'Morocco', 'easy-digital-downloads' ),
		'MZ' => __( 'Mozambique', 'easy-digital-downloads' ),
		'MM' => __( 'Myanmar', 'easy-digital-downloads' ),
		'NA' => __( 'Namibia', 'easy-digital-downloads' ),
		'NR' => __( 'Nauru', 'easy-digital-downloads' ),
		'NP' => __( 'Nepal', 'easy-digital-downloads' ),
		'NL' => __( 'Netherlands', 'easy-digital-downloads' ),
		'AN' => __( 'Netherlands Antilles', 'easy-digital-downloads' ),
		'NC' => __( 'New Caledonia', 'easy-digital-downloads' ),
		'NZ' => __( 'New Zealand', 'easy-digital-downloads' ),
		'NI' => __( 'Nicaragua', 'easy-digital-downloads' ),
		'NE' => __( 'Niger', 'easy-digital-downloads' ),
		'NG' => __( 'Nigeria', 'easy-digital-downloads' ),
		'NU' => __( 'Niue', 'easy-digital-downloads' ),
		'NF' => __( 'Norfolk Island', 'easy-digital-downloads' ),
		'KP' => __( 'North Korea', 'easy-digital-downloads' ),
		'MP' => __( 'Northern Mariana Islands', 'easy-digital-downloads' ),
		'NO' => __( 'Norway', 'easy-digital-downloads' ),
		'OM' => __( 'Oman', 'easy-digital-downloads' ),
		'PK' => __( 'Pakistan', 'easy-digital-downloads' ),
		'PW' => __( 'Palau', 'easy-digital-downloads' ),
		'PS' => __( 'Palestinian Territories', 'easy-digital-downloads' ),
		'PA' => __( 'Panama', 'easy-digital-downloads' ),
		'PG' => __( 'Papua New Guinea', 'easy-digital-downloads' ),
		'PY' => __( 'Paraguay', 'easy-digital-downloads' ),
		'PE' => __( 'Peru', 'easy-digital-downloads' ),
		'PH' => __( 'Philippines', 'easy-digital-downloads' ),
		'PN' => __( 'Pitcairn Island', 'easy-digital-downloads' ),
		'PL' => __( 'Poland', 'easy-digital-downloads' ),
		'PT' => __( 'Portugal', 'easy-digital-downloads' ),
		'PR' => __( 'Puerto Rico', 'easy-digital-downloads' ),
		'QA' => __( 'Qatar', 'easy-digital-downloads' ),
		'XK' => __( 'Republic of Kosovo', 'easy-digital-downloads' ),
		'RE' => __( 'Reunion Island', 'easy-digital-downloads' ),
		'RO' => __( 'Romania', 'easy-digital-downloads' ),
		'RU' => __( 'Russian Federation', 'easy-digital-downloads' ),
		'RW' => __( 'Rwanda', 'easy-digital-downloads' ),
		'BL' => __( 'Saint Barth&eacute;lemy', 'easy-digital-downloads' ),
		'SH' => __( 'Saint Helena', 'easy-digital-downloads' ),
		'KN' => __( 'Saint Kitts and Nevis', 'easy-digital-downloads' ),
		'LC' => __( 'Saint Lucia', 'easy-digital-downloads' ),
		'MF' => __( 'Saint Martin (French)', 'easy-digital-downloads' ),
		'SX' => __( 'Saint Martin (Dutch)', 'easy-digital-downloads' ),
		'PM' => __( 'Saint Pierre and Miquelon', 'easy-digital-downloads' ),
		'VC' => __( 'Saint Vincent and the Grenadines', 'easy-digital-downloads' ),
		'SM' => __( 'San Marino', 'easy-digital-downloads' ),
		'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'easy-digital-downloads' ),
		'SA' => __( 'Saudi Arabia', 'easy-digital-downloads' ),
		'SN' => __( 'Senegal', 'easy-digital-downloads' ),
		'RS' => __( 'Serbia', 'easy-digital-downloads' ),
		'SC' => __( 'Seychelles', 'easy-digital-downloads' ),
		'SL' => __( 'Sierra Leone', 'easy-digital-downloads' ),
		'SG' => __( 'Singapore', 'easy-digital-downloads' ),
		'SK' => __( 'Slovak Republic', 'easy-digital-downloads' ),
		'SI' => __( 'Slovenia', 'easy-digital-downloads' ),
		'SB' => __( 'Solomon Islands', 'easy-digital-downloads' ),
		'SO' => __( 'Somalia', 'easy-digital-downloads' ),
		'ZA' => __( 'South Africa', 'easy-digital-downloads' ),
		'GS' => __( 'South Georgia', 'easy-digital-downloads' ),
		'KR' => __( 'South Korea', 'easy-digital-downloads' ),
		'SS' => __( 'South Sudan', 'easy-digital-downloads' ),
		'ES' => __( 'Spain', 'easy-digital-downloads' ),
		'LK' => __( 'Sri Lanka', 'easy-digital-downloads' ),
		'SD' => __( 'Sudan', 'easy-digital-downloads' ),
		'SR' => __( 'Suriname', 'easy-digital-downloads' ),
		'SJ' => __( 'Svalbard and Jan Mayen Islands', 'easy-digital-downloads' ),
		'SZ' => __( 'Swaziland', 'easy-digital-downloads' ),
		'SE' => __( 'Sweden', 'easy-digital-downloads' ),
		'CH' => __( 'Switzerland', 'easy-digital-downloads' ),
		'SY' => __( 'Syrian Arab Republic', 'easy-digital-downloads' ),
		'TW' => __( 'Taiwan', 'easy-digital-downloads' ),
		'TJ' => __( 'Tajikistan', 'easy-digital-downloads' ),
		'TZ' => __( 'Tanzania', 'easy-digital-downloads' ),
		'TH' => __( 'Thailand', 'easy-digital-downloads' ),
		'TL' => __( 'Timor-Leste', 'easy-digital-downloads' ),
		'TG' => __( 'Togo', 'easy-digital-downloads' ),
		'TK' => __( 'Tokelau', 'easy-digital-downloads' ),
		'TO' => __( 'Tonga', 'easy-digital-downloads' ),
		'TT' => __( 'Trinidad and Tobago', 'easy-digital-downloads' ),
		'TN' => __( 'Tunisia', 'easy-digital-downloads' ),
		'TR' => __( 'Turkey', 'easy-digital-downloads' ),
		'TM' => __( 'Turkmenistan', 'easy-digital-downloads' ),
		'TC' => __( 'Turks and Caicos Islands', 'easy-digital-downloads' ),
		'TV' => __( 'Tuvalu', 'easy-digital-downloads' ),
		'UG' => __( 'Uganda', 'easy-digital-downloads' ),
		'UA' => __( 'Ukraine', 'easy-digital-downloads' ),
		'AE' => __( 'United Arab Emirates', 'easy-digital-downloads' ),
		'UY' => __( 'Uruguay', 'easy-digital-downloads' ),
		'UM' => __( 'US Minor Outlying Islands', 'easy-digital-downloads' ),
		'UZ' => __( 'Uzbekistan', 'easy-digital-downloads' ),
		'VU' => __( 'Vanuatu', 'easy-digital-downloads' ),
		'VE' => __( 'Venezuela', 'easy-digital-downloads' ),
		'VN' => __( 'Vietnam', 'easy-digital-downloads' ),
		'VG' => __( 'Virgin Islands (British)', 'easy-digital-downloads' ),
		'VI' => __( 'Virgin Islands (USA)', 'easy-digital-downloads' ),
		'WF' => __( 'Wallis and Futuna Islands', 'easy-digital-downloads' ),
		'EH' => __( 'Western Sahara', 'easy-digital-downloads' ),
		'WS' => __( 'Western Samoa', 'easy-digital-downloads' ),
		'YE' => __( 'Yemen', 'easy-digital-downloads' ),
		'ZM' => __( 'Zambia', 'easy-digital-downloads' ),
		'ZW' => __( 'Zimbabwe', 'easy-digital-downloads' ),
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
	$states = array(
		''   => '',
		'AL' => __( 'Alabama', 'easy-digital-downloads' ),
		'AK' => __( 'Alaska', 'easy-digital-downloads' ),
		'AZ' => __( 'Arizona', 'easy-digital-downloads' ),
		'AR' => __( 'Arkansas', 'easy-digital-downloads' ),
		'CA' => __( 'California', 'easy-digital-downloads' ),
		'CO' => __( 'Colorado', 'easy-digital-downloads' ),
		'CT' => __( 'Connecticut', 'easy-digital-downloads' ),
		'DE' => __( 'Delaware', 'easy-digital-downloads' ),
		'DC' => __( 'District of Columbia', 'easy-digital-downloads' ),
		'FL' => __( 'Florida', 'easy-digital-downloads' ),
		'GA' => __( 'Georgia', 'easy-digital-downloads' ),
		'HI' => __( 'Hawaii', 'easy-digital-downloads' ),
		'ID' => __( 'Idaho', 'easy-digital-downloads' ),
		'IL' => __( 'Illinois', 'easy-digital-downloads' ),
		'IN' => __( 'Indiana', 'easy-digital-downloads' ),
		'IA' => __( 'Iowa', 'easy-digital-downloads' ),
		'KS' => __( 'Kansas', 'easy-digital-downloads' ),
		'KY' => __( 'Kentucky', 'easy-digital-downloads' ),
		'LA' => __( 'Louisiana', 'easy-digital-downloads' ),
		'ME' => __( 'Maine', 'easy-digital-downloads' ),
		'MD' => __( 'Maryland', 'easy-digital-downloads' ),
		'MA' => __( 'Massachusetts', 'easy-digital-downloads' ),
		'MI' => __( 'Michigan', 'easy-digital-downloads' ),
		'MN' => __( 'Minnesota', 'easy-digital-downloads' ),
		'MS' => __( 'Mississippi', 'easy-digital-downloads' ),
		'MO' => __( 'Missouri', 'easy-digital-downloads' ),
		'MT' => __( 'Montana', 'easy-digital-downloads' ),
		'NE' => __( 'Nebraska', 'easy-digital-downloads' ),
		'NV' => __( 'Nevada', 'easy-digital-downloads' ),
		'NH' => __( 'New Hampshire', 'easy-digital-downloads' ),
		'NJ' => __( 'New Jersey', 'easy-digital-downloads' ),
		'NM' => __( 'New Mexico', 'easy-digital-downloads' ),
		'NY' => __( 'New York', 'easy-digital-downloads' ),
		'NC' => __( 'North Carolina', 'easy-digital-downloads' ),
		'ND' => __( 'North Dakota', 'easy-digital-downloads' ),
		'OH' => __( 'Ohio', 'easy-digital-downloads' ),
		'OK' => __( 'Oklahoma', 'easy-digital-downloads' ),
		'OR' => __( 'Oregon', 'easy-digital-downloads' ),
		'PA' => __( 'Pennsylvania', 'easy-digital-downloads' ),
		'RI' => __( 'Rhode Island', 'easy-digital-downloads' ),
		'SC' => __( 'South Carolina', 'easy-digital-downloads' ),
		'SD' => __( 'South Dakota', 'easy-digital-downloads' ),
		'TN' => __( 'Tennessee', 'easy-digital-downloads' ),
		'TX' => __( 'Texas', 'easy-digital-downloads' ),
		'UT' => __( 'Utah', 'easy-digital-downloads' ),
		'VT' => __( 'Vermont', 'easy-digital-downloads' ),
		'VA' => __( 'Virginia', 'easy-digital-downloads' ),
		'WA' => __( 'Washington', 'easy-digital-downloads' ),
		'WV' => __( 'West Virginia', 'easy-digital-downloads' ),
		'WI' => __( 'Wisconsin', 'easy-digital-downloads' ),
		'WY' => __( 'Wyoming', 'easy-digital-downloads' ),
		'AS' => __( 'American Samoa', 'easy-digital-downloads' ),
		'CZ' => __( 'Canal Zone', 'easy-digital-downloads' ),
		'CM' => __( 'Commonwealth of the Northern Mariana Islands', 'easy-digital-downloads' ),
		'FM' => __( 'Federated States of Micronesia', 'easy-digital-downloads' ),
		'GU' => __( 'Guam', 'easy-digital-downloads' ),
		'MH' => __( 'Marshall Islands', 'easy-digital-downloads' ),
		'MP' => __( 'Northern Mariana Islands', 'easy-digital-downloads' ),
		'PW' => __( 'Palau', 'easy-digital-downloads' ),
		'PI' => __( 'Philippine Islands', 'easy-digital-downloads' ),
		'PR' => __( 'Puerto Rico', 'easy-digital-downloads' ),
		'TT' => __( 'Trust Territory of the Pacific Islands', 'easy-digital-downloads' ),
		'VI' => __( 'Virgin Islands', 'easy-digital-downloads' ),
		'AA' => __( 'Armed Forces - Americas', 'easy-digital-downloads' ),
		'AE' => __( 'Armed Forces - Europe, Canada, Middle East, Africa', 'easy-digital-downloads' ),
		'AP' => __( 'Armed Forces - Pacific', 'easy-digital-downloads' ),
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
		''   => '',
		'AB' => __( 'Alberta', 'easy-digital-downloads' ),
		'BC' => __( 'British Columbia', 'easy-digital-downloads' ),
		'MB' => __( 'Manitoba', 'easy-digital-downloads' ),
		'NB' => __( 'New Brunswick', 'easy-digital-downloads' ),
		'NL' => __( 'Newfoundland and Labrador', 'easy-digital-downloads' ),
		'NS' => __( 'Nova Scotia', 'easy-digital-downloads' ),
		'NT' => __( 'Northwest Territories', 'easy-digital-downloads' ),
		'NU' => __( 'Nunavut', 'easy-digital-downloads' ),
		'ON' => __( 'Ontario', 'easy-digital-downloads' ),
		'PE' => __( 'Prince Edward Island', 'easy-digital-downloads' ),
		'QC' => __( 'Quebec', 'easy-digital-downloads' ),
		'SK' => __( 'Saskatchewan', 'easy-digital-downloads' ),
		'YT' => __( 'Yukon', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_canada_provinces', $provinces );
}

/**
 * Get Australian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_australian_states_list() {
	$states = array(
		''    => '',
		'ACT' => __( 'Australian Capital Territory', 'easy-digital-downloads' ),
		'NSW' => __( 'New South Wales', 'easy-digital-downloads' ),
		'NT'  => __( 'Northern Territory', 'easy-digital-downloads' ),
		'QLD' => __( 'Queensland', 'easy-digital-downloads' ),
		'SA'  => __( 'South Australia', 'easy-digital-downloads' ),
		'TAS' => __( 'Tasmania', 'easy-digital-downloads' ),
		'VIC' => __( 'Victoria', 'easy-digital-downloads' ),
		'WA'  => __( 'Western Australia', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_australian_states', $states );
}

/**
 * Get Bangladeshi States (districts)
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_bangladeshi_states_list() {
	$states = array(
		''    => '',
		'BAG' => __( 'Bagerhat', 'easy-digital-downloads' ),
		'BAN' => __( 'Bandarban', 'easy-digital-downloads' ),
		'BAR' => __( 'Barguna', 'easy-digital-downloads' ),
		'BARI'=> __( 'Barisal', 'easy-digital-downloads' ),
		'BHO' => __( 'Bhola', 'easy-digital-downloads' ),
		'BOG' => __( 'Bogra', 'easy-digital-downloads' ),
		'BRA' => __( 'Brahmanbaria', 'easy-digital-downloads' ),
		'CHA' => __( 'Chandpur', 'easy-digital-downloads' ),
		'CHI' => __( 'Chittagong', 'easy-digital-downloads' ),
		'CHU' => __( 'Chuadanga', 'easy-digital-downloads' ),
		'COM' => __( 'Comilla', 'easy-digital-downloads' ),
		'COX' => __( 'Cox\'s Bazar', 'easy-digital-downloads' ),
		'DHA' => __( 'Dhaka', 'easy-digital-downloads' ),
		'DIN' => __( 'Dinajpur', 'easy-digital-downloads' ),
		'FAR' => __( 'Faridpur', 'easy-digital-downloads' ),
		'FEN' => __( 'Feni', 'easy-digital-downloads' ),
		'GAI' => __( 'Gaibandha', 'easy-digital-downloads' ),
		'GAZI'=> __( 'Gazipur', 'easy-digital-downloads' ),
		'GOP' => __( 'Gopalganj', 'easy-digital-downloads' ),
		'HAB' => __( 'Habiganj', 'easy-digital-downloads' ),
		'JAM' => __( 'Jamalpur', 'easy-digital-downloads' ),
		'JES' => __( 'Jessore', 'easy-digital-downloads' ),
		'JHA' => __( 'Jhalokati', 'easy-digital-downloads' ),
		'JHE' => __( 'Jhenaidah', 'easy-digital-downloads' ),
		'JOY' => __( 'Joypurhat', 'easy-digital-downloads' ),
		'KHA' => __( 'Khagrachhari', 'easy-digital-downloads' ),
		'KHU' => __( 'Khulna', 'easy-digital-downloads' ),
		'KIS' => __( 'Kishoreganj', 'easy-digital-downloads' ),
		'KUR' => __( 'Kurigram', 'easy-digital-downloads' ),
		'KUS' => __( 'Kushtia', 'easy-digital-downloads' ),
		'LAK' => __( 'Lakshmipur', 'easy-digital-downloads' ),
		'LAL' => __( 'Lalmonirhat', 'easy-digital-downloads' ),
		'MAD' => __( 'Madaripur', 'easy-digital-downloads' ),
		'MAG' => __( 'Magura', 'easy-digital-downloads' ),
		'MAN' => __( 'Manikganj', 'easy-digital-downloads' ),
		'MEH' => __( 'Meherpur', 'easy-digital-downloads' ),
		'MOU' => __( 'Moulvibazar', 'easy-digital-downloads' ),
		'MUN' => __( 'Munshiganj', 'easy-digital-downloads' ),
		'MYM' => __( 'Mymensingh', 'easy-digital-downloads' ),
		'NAO' => __( 'Naogaon', 'easy-digital-downloads' ),
		'NAR' => __( 'Narail', 'easy-digital-downloads' ),
		'NARG'=> __( 'Narayanganj', 'easy-digital-downloads' ),
		'NARD'=> __( 'Narsingdi', 'easy-digital-downloads' ),
		'NAT' => __( 'Natore', 'easy-digital-downloads' ),
		'NAW' => __( 'Nawabganj', 'easy-digital-downloads' ),
		'NET' => __( 'Netrakona', 'easy-digital-downloads' ),
		'NIL' => __( 'Nilphamari', 'easy-digital-downloads' ),
		'NOA' => __( 'Noakhali', 'easy-digital-downloads' ),
		'PAB' => __( 'Pabna', 'easy-digital-downloads' ),
		'PAN' => __( 'Panchagarh', 'easy-digital-downloads' ),
		'PAT' => __( 'Patuakhali', 'easy-digital-downloads' ),
		'PIR' => __( 'Pirojpur', 'easy-digital-downloads' ),
		'RAJB'=> __( 'Rajbari', 'easy-digital-downloads' ),
		'RAJ' => __( 'Rajshahi', 'easy-digital-downloads' ),
		'RAN' => __( 'Rangamati', 'easy-digital-downloads' ),
		'RANP'=> __( 'Rangpur', 'easy-digital-downloads' ),
		'SAT' => __( 'Satkhira', 'easy-digital-downloads' ),
		'SHA' => __( 'Shariatpur', 'easy-digital-downloads' ),
		'SHE' => __( 'Sherpur', 'easy-digital-downloads' ),
		'SIR' => __( 'Sirajganj', 'easy-digital-downloads' ),
		'SUN' => __( 'Sunamganj', 'easy-digital-downloads' ),
		'SYL' => __( 'Sylhet', 'easy-digital-downloads' ),
		'TAN' => __( 'Tangail', 'easy-digital-downloads' ),
		'THA' => __( 'Thakurgaon', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_bangladeshi_states', $states );
}

/**
 * Get Brazil States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_brazil_states_list() {
	$states = array(
		''   => '',
		'AC' => __( 'Acre', 'easy-digital-downloads' ),
		'AL' => __( 'Alagoas', 'easy-digital-downloads' ),
		'AP' => __( 'Amap&aacute;', 'easy-digital-downloads' ),
		'AM' => __( 'Amazonas', 'easy-digital-downloads' ),
		'BA' => __( 'Bahia', 'easy-digital-downloads' ),
		'CE' => __( 'Cear&aacute;', 'easy-digital-downloads' ),
		'DF' => __( 'Distrito Federal', 'easy-digital-downloads' ),
		'ES' => __( 'Esp&iacute;rito Santo', 'easy-digital-downloads' ),
		'GO' => __( 'Goi&aacute;s', 'easy-digital-downloads' ),
		'MA' => __( 'Maranh&atilde;o', 'easy-digital-downloads' ),
		'MT' => __( 'Mato Grosso', 'easy-digital-downloads' ),
		'MS' => __( 'Mato Grosso do Sul', 'easy-digital-downloads' ),
		'MG' => __( 'Minas Gerais', 'easy-digital-downloads' ),
		'PA' => __( 'Par&aacute;', 'easy-digital-downloads' ),
		'PB' => __( 'Para&iacute;ba', 'easy-digital-downloads' ),
		'PR' => __( 'Paran&aacute;', 'easy-digital-downloads' ),
		'PE' => __( 'Pernambuco', 'easy-digital-downloads' ),
		'PI' => __( 'Piau&iacute;', 'easy-digital-downloads' ),
		'RJ' => __( 'Rio de Janeiro', 'easy-digital-downloads' ),
		'RN' => __( 'Rio Grande do Norte', 'easy-digital-downloads' ),
		'RS' => __( 'Rio Grande do Sul', 'easy-digital-downloads' ),
		'RO' => __( 'Rond&ocirc;nia', 'easy-digital-downloads' ),
		'RR' => __( 'Roraima', 'easy-digital-downloads' ),
		'SC' => __( 'Santa Catarina', 'easy-digital-downloads' ),
		'SP' => __( 'S&atilde;o Paulo', 'easy-digital-downloads' ),
		'SE' => __( 'Sergipe', 'easy-digital-downloads' ),
		'TO' => __( 'Tocantins', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_brazil_states', $states );
}

/**
 * Get Bulgarian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_bulgarian_states_list() {
	$states = array(
		''      => '',
		'BG-01' => __( 'Blagoevgrad', 'easy-digital-downloads' ),
		'BG-02' => __( 'Burgas', 'easy-digital-downloads' ),
		'BG-08' => __( 'Dobrich', 'easy-digital-downloads' ),
		'BG-07' => __( 'Gabrovo', 'easy-digital-downloads' ),
		'BG-26' => __( 'Haskovo', 'easy-digital-downloads' ),
		'BG-09' => __( 'Kardzhali', 'easy-digital-downloads' ),
		'BG-10' => __( 'Kyustendil', 'easy-digital-downloads' ),
		'BG-11' => __( 'Lovech', 'easy-digital-downloads' ),
		'BG-12' => __( 'Montana', 'easy-digital-downloads' ),
		'BG-13' => __( 'Pazardzhik', 'easy-digital-downloads' ),
		'BG-14' => __( 'Pernik', 'easy-digital-downloads' ),
		'BG-15' => __( 'Pleven', 'easy-digital-downloads' ),
		'BG-16' => __( 'Plovdiv', 'easy-digital-downloads' ),
		'BG-17' => __( 'Razgrad', 'easy-digital-downloads' ),
		'BG-18' => __( 'Ruse', 'easy-digital-downloads' ),
		'BG-27' => __( 'Shumen', 'easy-digital-downloads' ),
		'BG-19' => __( 'Silistra', 'easy-digital-downloads' ),
		'BG-20' => __( 'Sliven', 'easy-digital-downloads' ),
		'BG-21' => __( 'Smolyan', 'easy-digital-downloads' ),
		'BG-23' => __( 'Sofia', 'easy-digital-downloads' ),
		'BG-22' => __( 'Sofia-Grad', 'easy-digital-downloads' ),
		'BG-24' => __( 'Stara Zagora', 'easy-digital-downloads' ),
		'BG-25' => __( 'Targovishte', 'easy-digital-downloads' ),
		'BG-03' => __( 'Varna', 'easy-digital-downloads' ),
		'BG-04' => __( 'Veliko Tarnovo', 'easy-digital-downloads' ),
		'BG-05' => __( 'Vidin', 'easy-digital-downloads' ),
		'BG-06' => __( 'Vratsa', 'easy-digital-downloads' ),
		'BG-28' => __( 'Yambol', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_bulgarian_states', $states );
}

/**
 * Get Hong Kong States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_hong_kong_states_list() {
	$states = array(
		''                => '',
		'HONG KONG'       => __( 'Hong Kong Island', 'easy-digital-downloads' ),
		'KOWLOON'         => __( 'Kowloon', 'easy-digital-downloads' ),
		'NEW TERRITORIES' => __( 'New Territories', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_hong_kong_states', $states );
}

/**
 * Get Hungary States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_hungary_states_list() {
	$states = array(
		''   => '',
		'BK' => __( 'Bács-Kiskun', 'easy-digital-downloads' ),
		'BE' => __( 'Békés', 'easy-digital-downloads' ),
		'BA' => __( 'Baranya', 'easy-digital-downloads' ),
		'BZ' => __( 'Borsod-Abaúj-Zemplén', 'easy-digital-downloads' ),
		'BU' => __( 'Budapest', 'easy-digital-downloads' ),
		'CS' => __( 'Csongrád', 'easy-digital-downloads' ),
		'FE' => __( 'Fejér', 'easy-digital-downloads' ),
		'GS' => __( 'Győr-Moson-Sopron', 'easy-digital-downloads' ),
		'HB' => __( 'Hajdú-Bihar', 'easy-digital-downloads' ),
		'HE' => __( 'Heves', 'easy-digital-downloads' ),
		'JN' => __( 'Jász-Nagykun-Szolnok', 'easy-digital-downloads' ),
		'KE' => __( 'Komárom-Esztergom', 'easy-digital-downloads' ),
		'NO' => __( 'Nógrád', 'easy-digital-downloads' ),
		'PE' => __( 'Pest', 'easy-digital-downloads' ),
		'SO' => __( 'Somogy', 'easy-digital-downloads' ),
		'SZ' => __( 'Szabolcs-Szatmár-Bereg', 'easy-digital-downloads' ),
		'TO' => __( 'Tolna', 'easy-digital-downloads' ),
		'VA' => __( 'Vas', 'easy-digital-downloads' ),
		'VE' => __( 'Veszprém', 'easy-digital-downloads' ),
		'ZA' => __( 'Zala', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_hungary_states', $states );
}

/**
 * Get Japanese States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_japanese_states_list() {
	$states = array(
		''     => '',
		'JP01' => __( 'Hokkaido', 'easy-digital-downloads' ),
		'JP02' => __( 'Aomori', 'easy-digital-downloads' ),
		'JP03' => __( 'Iwate', 'easy-digital-downloads' ),
		'JP04' => __( 'Miyagi', 'easy-digital-downloads' ),
		'JP05' => __( 'Akita', 'easy-digital-downloads' ),
		'JP06' => __( 'Yamagata', 'easy-digital-downloads' ),
		'JP07' => __( 'Fukushima', 'easy-digital-downloads' ),
		'JP08' => __( 'Ibaraki', 'easy-digital-downloads' ),
		'JP09' => __( 'Tochigi', 'easy-digital-downloads' ),
		'JP10' => __( 'Gunma', 'easy-digital-downloads' ),
		'JP11' => __( 'Saitama', 'easy-digital-downloads' ),
		'JP12' => __( 'Chiba', 'easy-digital-downloads' ),
		'JP13' => __( 'Tokyo', 'easy-digital-downloads' ),
		'JP14' => __( 'Kanagawa', 'easy-digital-downloads' ),
		'JP15' => __( 'Niigata', 'easy-digital-downloads' ),
		'JP16' => __( 'Toyama', 'easy-digital-downloads' ),
		'JP17' => __( 'Ishikawa', 'easy-digital-downloads' ),
		'JP18' => __( 'Fukui', 'easy-digital-downloads' ),
		'JP19' => __( 'Yamanashi', 'easy-digital-downloads' ),
		'JP20' => __( 'Nagano', 'easy-digital-downloads' ),
		'JP21' => __( 'Gifu', 'easy-digital-downloads' ),
		'JP22' => __( 'Shizuoka', 'easy-digital-downloads' ),
		'JP23' => __( 'Aichi', 'easy-digital-downloads' ),
		'JP24' => __( 'Mie', 'easy-digital-downloads' ),
		'JP25' => __( 'Shiga', 'easy-digital-downloads' ),
		'JP26' => __( 'Kyouto', 'easy-digital-downloads' ),
		'JP27' => __( 'Osaka', 'easy-digital-downloads' ),
		'JP28' => __( 'Hyougo', 'easy-digital-downloads' ),
		'JP29' => __( 'Nara', 'easy-digital-downloads' ),
		'JP30' => __( 'Wakayama', 'easy-digital-downloads' ),
		'JP31' => __( 'Tottori', 'easy-digital-downloads' ),
		'JP32' => __( 'Shimane', 'easy-digital-downloads' ),
		'JP33' => __( 'Okayama', 'easy-digital-downloads' ),
		'JP34' => __( 'Hiroshima', 'easy-digital-downloads' ),
		'JP35' => __( 'Yamaguchi', 'easy-digital-downloads' ),
		'JP36' => __( 'Tokushima', 'easy-digital-downloads' ),
		'JP37' => __( 'Kagawa', 'easy-digital-downloads' ),
		'JP38' => __( 'Ehime', 'easy-digital-downloads' ),
		'JP39' => __( 'Kochi', 'easy-digital-downloads' ),
		'JP40' => __( 'Fukuoka', 'easy-digital-downloads' ),
		'JP41' => __( 'Saga', 'easy-digital-downloads' ),
		'JP42' => __( 'Nagasaki', 'easy-digital-downloads' ),
		'JP43' => __( 'Kumamoto', 'easy-digital-downloads' ),
		'JP44' => __( 'Oita', 'easy-digital-downloads' ),
		'JP45' => __( 'Miyazaki', 'easy-digital-downloads' ),
		'JP46' => __( 'Kagoshima', 'easy-digital-downloads' ),
		'JP47' => __( 'Okinawa', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_japanese_states', $states );
}

/**
 * Get Chinese States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_chinese_states_list() {
	$states = array(
		''     => '',
	    'CN1'  => __( 'Yunnan / &#20113;&#21335;', 'easy-digital-downloads' ),
	    'CN2'  => __( 'Beijing / &#21271;&#20140;', 'easy-digital-downloads' ),
	    'CN3'  => __( 'Tianjin / &#22825;&#27941;', 'easy-digital-downloads' ),
	    'CN4'  => __( 'Hebei / &#27827;&#21271;', 'easy-digital-downloads' ),
	    'CN5'  => __( 'Shanxi / &#23665;&#35199;', 'easy-digital-downloads' ),
	    'CN6'  => __( 'Inner Mongolia / &#20839;&#33945;&#21476;', 'easy-digital-downloads' ),
	    'CN7'  => __( 'Liaoning / &#36797;&#23425;', 'easy-digital-downloads' ),
	    'CN8'  => __( 'Jilin / &#21513;&#26519;', 'easy-digital-downloads' ),
	    'CN9'  => __( 'Heilongjiang / &#40657;&#40857;&#27743;', 'easy-digital-downloads' ),
	    'CN10' => __( 'Shanghai / &#19978;&#28023;', 'easy-digital-downloads' ),
	    'CN11' => __( 'Jiangsu / &#27743;&#33487;', 'easy-digital-downloads' ),
	    'CN12' => __( 'Zhejiang / &#27993;&#27743;', 'easy-digital-downloads' ),
	    'CN13' => __( 'Anhui / &#23433;&#24509;', 'easy-digital-downloads' ),
	    'CN14' => __( 'Fujian / &#31119;&#24314;', 'easy-digital-downloads' ),
	    'CN15' => __( 'Jiangxi / &#27743;&#35199;', 'easy-digital-downloads' ),
	    'CN16' => __( 'Shandong / &#23665;&#19996;', 'easy-digital-downloads' ),
	    'CN17' => __( 'Henan / &#27827;&#21335;', 'easy-digital-downloads' ),
	    'CN18' => __( 'Hubei / &#28246;&#21271;', 'easy-digital-downloads' ),
	    'CN19' => __( 'Hunan / &#28246;&#21335;', 'easy-digital-downloads' ),
	    'CN20' => __( 'Guangdong / &#24191;&#19996;', 'easy-digital-downloads' ),
	    'CN21' => __( 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;', 'easy-digital-downloads' ),
	    'CN22' => __( 'Hainan / &#28023;&#21335;', 'easy-digital-downloads' ),
	    'CN23' => __( 'Chongqing / &#37325;&#24198;', 'easy-digital-downloads' ),
	    'CN24' => __( 'Sichuan / &#22235;&#24029;', 'easy-digital-downloads' ),
	    'CN25' => __( 'Guizhou / &#36149;&#24030;', 'easy-digital-downloads' ),
	    'CN26' => __( 'Shaanxi / &#38485;&#35199;', 'easy-digital-downloads' ),
	    'CN27' => __( 'Gansu / &#29976;&#32899;', 'easy-digital-downloads' ),
	    'CN28' => __( 'Qinghai / &#38738;&#28023;', 'easy-digital-downloads' ),
	    'CN29' => __( 'Ningxia Hui / &#23425;&#22799;', 'easy-digital-downloads' ),
	    'CN30' => __( 'Macau / &#28595;&#38376;', 'easy-digital-downloads' ),
	    'CN31' => __( 'Tibet / &#35199;&#34255;', 'easy-digital-downloads' ),
	    'CN32' => __( 'Xinjiang / &#26032;&#30086;', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_chinese_states', $states );
}

/**
 * Get New Zealand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_new_zealand_states_list() {
	$states = array(
		''   => '',
		'AK' => __( 'Auckland', 'easy-digital-downloads' ),
		'BP' => __( 'Bay of Plenty', 'easy-digital-downloads' ),
		'CT' => __( 'Canterbury', 'easy-digital-downloads' ),
		'HB' => __( 'Hawke&rsquo;s Bay', 'easy-digital-downloads' ),
		'MW' => __( 'Manawatu-Wanganui', 'easy-digital-downloads' ),
		'MB' => __( 'Marlborough', 'easy-digital-downloads' ),
		'NS' => __( 'Nelson', 'easy-digital-downloads' ),
		'NL' => __( 'Northland', 'easy-digital-downloads' ),
		'OT' => __( 'Otago', 'easy-digital-downloads' ),
		'SL' => __( 'Southland', 'easy-digital-downloads' ),
		'TK' => __( 'Taranaki', 'easy-digital-downloads' ),
		'TM' => __( 'Tasman', 'easy-digital-downloads' ),
		'WA' => __( 'Waikato', 'easy-digital-downloads' ),
		'WR' => __( 'Wairarapa', 'easy-digital-downloads' ),
		'WE' => __( 'Wellington', 'easy-digital-downloads' ),
		'WC' => __( 'West Coast', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_new_zealand_states', $states );
}

/**
 * Get Peruvian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_peruvian_states_list() {
	$states = array(
		''    => '',
		'CAL' => __( 'El Callao', 'easy-digital-downloads' ),
		'LMA' => __( 'Municipalidad Metropolitana de Lima', 'easy-digital-downloads' ),
		'AMA' => __( 'Amazonas', 'easy-digital-downloads' ),
		'ANC' => __( 'Ancash', 'easy-digital-downloads' ),
		'APU' => __( 'Apur&iacute;mac', 'easy-digital-downloads' ),
		'ARE' => __( 'Arequipa', 'easy-digital-downloads' ),
		'AYA' => __( 'Ayacucho', 'easy-digital-downloads' ),
		'CAJ' => __( 'Cajamarca', 'easy-digital-downloads' ),
		'CUS' => __( 'Cusco', 'easy-digital-downloads' ),
		'HUV' => __( 'Huancavelica', 'easy-digital-downloads' ),
		'HUC' => __( 'Hu&aacute;nuco', 'easy-digital-downloads' ),
		'ICA' => __( 'Ica', 'easy-digital-downloads' ),
		'JUN' => __( 'Jun&iacute;n', 'easy-digital-downloads' ),
		'LAL' => __( 'La Libertad', 'easy-digital-downloads' ),
		'LAM' => __( 'Lambayeque', 'easy-digital-downloads' ),
		'LIM' => __( 'Lima', 'easy-digital-downloads' ),
		'LOR' => __( 'Loreto', 'easy-digital-downloads' ),
		'MDD' => __( 'Madre de Dios', 'easy-digital-downloads' ),
		'MOQ' => __( 'Moquegua', 'easy-digital-downloads' ),
		'PAS' => __( 'Pasco', 'easy-digital-downloads' ),
		'PIU' => __( 'Piura', 'easy-digital-downloads' ),
		'PUN' => __( 'Puno', 'easy-digital-downloads' ),
		'SAM' => __( 'San Mart&iacute;n', 'easy-digital-downloads' ),
		'TAC' => __( 'Tacna', 'easy-digital-downloads' ),
		'TUM' => __( 'Tumbes', 'easy-digital-downloads' ),
		'UCA' => __( 'Ucayali', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_peruvian_states', $states );
}

/**
 * Get Indonesian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_indonesian_states_list() {
	$states  = array(
		''   => '',
		'AC' => __( 'Daerah Istimewa Aceh', 'easy-digital-downloads' ),
	    'SU' => __( 'Sumatera Utara', 'easy-digital-downloads' ),
	    'SB' => __( 'Sumatera Barat', 'easy-digital-downloads' ),
	    'RI' => __( 'Riau', 'easy-digital-downloads' ),
	    'KR' => __( 'Kepulauan Riau', 'easy-digital-downloads' ),
	    'JA' => __( 'Jambi', 'easy-digital-downloads' ),
	    'SS' => __( 'Sumatera Selatan', 'easy-digital-downloads' ),
	    'BB' => __( 'Bangka Belitung', 'easy-digital-downloads' ),
	    'BE' => __( 'Bengkulu', 'easy-digital-downloads' ),
	    'LA' => __( 'Lampung', 'easy-digital-downloads' ),
	    'JK' => __( 'DKI Jakarta', 'easy-digital-downloads' ),
	    'JB' => __( 'Jawa Barat', 'easy-digital-downloads' ),
	    'BT' => __( 'Banten', 'easy-digital-downloads' ),
	    'JT' => __( 'Jawa Tengah', 'easy-digital-downloads' ),
	    'JI' => __( 'Jawa Timur', 'easy-digital-downloads' ),
	    'YO' => __( 'Daerah Istimewa Yogyakarta', 'easy-digital-downloads' ),
	    'BA' => __( 'Bali', 'easy-digital-downloads' ),
	    'NB' => __( 'Nusa Tenggara Barat', 'easy-digital-downloads' ),
	    'NT' => __( 'Nusa Tenggara Timur', 'easy-digital-downloads' ),
	    'KB' => __( 'Kalimantan Barat', 'easy-digital-downloads' ),
	    'KT' => __( 'Kalimantan Tengah', 'easy-digital-downloads' ),
	    'KI' => __( 'Kalimantan Timur', 'easy-digital-downloads' ),
	    'KS' => __( 'Kalimantan Selatan', 'easy-digital-downloads' ),
	    'KU' => __( 'Kalimantan Utara', 'easy-digital-downloads' ),
	    'SA' => __( 'Sulawesi Utara', 'easy-digital-downloads' ),
	    'ST' => __( 'Sulawesi Tengah', 'easy-digital-downloads' ),
	    'SG' => __( 'Sulawesi Tenggara', 'easy-digital-downloads' ),
	    'SR' => __( 'Sulawesi Barat', 'easy-digital-downloads' ),
	    'SN' => __( 'Sulawesi Selatan', 'easy-digital-downloads' ),
	    'GO' => __( 'Gorontalo', 'easy-digital-downloads' ),
	    'MA' => __( 'Maluku', 'easy-digital-downloads' ),
	    'MU' => __( 'Maluku Utara', 'easy-digital-downloads' ),
	    'PA' => __( 'Papua', 'easy-digital-downloads' ),
	    'PB' => __( 'Papua Barat', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_indonesia_states', $states );
}

/**
 * Get Indian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_indian_states_list() {
	$states = array(
		''   => '',
		'AP' => __( 'Andhra Pradesh', 'easy-digital-downloads' ),
		'AR' => __( 'Arunachal Pradesh', 'easy-digital-downloads' ),
		'AS' => __( 'Assam', 'easy-digital-downloads' ),
		'BR' => __( 'Bihar', 'easy-digital-downloads' ),
		'CT' => __( 'Chhattisgarh', 'easy-digital-downloads' ),
		'GA' => __( 'Goa', 'easy-digital-downloads' ),
		'GJ' => __( 'Gujarat', 'easy-digital-downloads' ),
		'HR' => __( 'Haryana', 'easy-digital-downloads' ),
		'HP' => __( 'Himachal Pradesh', 'easy-digital-downloads' ),
		'JK' => __( 'Jammu and Kashmir', 'easy-digital-downloads' ),
		'JH' => __( 'Jharkhand', 'easy-digital-downloads' ),
		'KA' => __( 'Karnataka', 'easy-digital-downloads' ),
		'KL' => __( 'Kerala', 'easy-digital-downloads' ),
		'MP' => __( 'Madhya Pradesh', 'easy-digital-downloads' ),
		'MH' => __( 'Maharashtra', 'easy-digital-downloads' ),
		'MN' => __( 'Manipur', 'easy-digital-downloads' ),
		'ML' => __( 'Meghalaya', 'easy-digital-downloads' ),
		'MZ' => __( 'Mizoram', 'easy-digital-downloads' ),
		'NL' => __( 'Nagaland', 'easy-digital-downloads' ),
		'OR' => __( 'Orissa', 'easy-digital-downloads' ),
		'PB' => __( 'Punjab', 'easy-digital-downloads' ),
		'RJ' => __( 'Rajasthan', 'easy-digital-downloads' ),
		'SK' => __( 'Sikkim', 'easy-digital-downloads' ),
		'TN' => __( 'Tamil Nadu', 'easy-digital-downloads' ),
		'TG' => __( 'Telangana', 'easy-digital-downloads' ),
		'TR' => __( 'Tripura', 'easy-digital-downloads' ),
		'UT' => __( 'Uttarakhand', 'easy-digital-downloads' ),
		'UP' => __( 'Uttar Pradesh', 'easy-digital-downloads' ),
		'WB' => __( 'West Bengal', 'easy-digital-downloads' ),
		'AN' => __( 'Andaman and Nicobar Islands', 'easy-digital-downloads' ),
		'CH' => __( 'Chandigarh', 'easy-digital-downloads' ),
		'DN' => __( 'Dadar and Nagar Haveli', 'easy-digital-downloads' ),
		'DD' => __( 'Daman and Diu', 'easy-digital-downloads' ),
		'DL' => __( 'Delhi', 'easy-digital-downloads' ),
		'LD' => __( 'Lakshadweep', 'easy-digital-downloads' ),
		'PY' => __( 'Pondicherry (Puducherry)', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_indian_states', $states );
}

/**
 * Get Iranian States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_iranian_states_list() {
	$states = array(
		''    => '',
		'KHZ' => __( 'Khuzestan', 'easy-digital-downloads' ),
		'THR' => __( 'Tehran', 'easy-digital-downloads' ),
		'ILM' => __( 'Ilaam', 'easy-digital-downloads' ),
		'BHR' => __( 'Bushehr', 'easy-digital-downloads' ),
		'ADL' => __( 'Ardabil', 'easy-digital-downloads' ),
		'ESF' => __( 'Isfahan', 'easy-digital-downloads' ),
		'YZD' => __( 'Yazd', 'easy-digital-downloads' ),
		'KRH' => __( 'Kermanshah', 'easy-digital-downloads' ),
		'KRN' => __( 'Kerman', 'easy-digital-downloads' ),
		'HDN' => __( 'Hamadan', 'easy-digital-downloads' ),
		'GZN' => __( 'Ghazvin', 'easy-digital-downloads' ),
		'ZJN' => __( 'Zanjan', 'easy-digital-downloads' ),
		'LRS' => __( 'Luristan', 'easy-digital-downloads' ),
		'ABZ' => __( 'Alborz', 'easy-digital-downloads' ),
		'EAZ' => __( 'East Azerbaijan', 'easy-digital-downloads' ),
		'WAZ' => __( 'West Azerbaijan', 'easy-digital-downloads' ),
		'CHB' => __( 'Chaharmahal and Bakhtiari', 'easy-digital-downloads' ),
		'SKH' => __( 'South Khorasan', 'easy-digital-downloads' ),
		'RKH' => __( 'Razavi Khorasan', 'easy-digital-downloads' ),
		'NKH' => __( 'North Khorasan', 'easy-digital-downloads' ),
		'SMN' => __( 'Semnan', 'easy-digital-downloads' ),
		'FRS' => __( 'Fars', 'easy-digital-downloads' ),
		'QHM' => __( 'Qom', 'easy-digital-downloads' ),
		'KRD' => __( 'Kurdistan', 'easy-digital-downloads' ),
		'KBD' => __( 'Kohgiluyeh and BoyerAhmad', 'easy-digital-downloads' ),
		'GLS' => __( 'Golestan', 'easy-digital-downloads' ),
		'GIL' => __( 'Gilan', 'easy-digital-downloads' ),
		'MZN' => __( 'Mazandaran', 'easy-digital-downloads' ),
		'MKZ' => __( 'Markazi', 'easy-digital-downloads' ),
		'HRZ' => __( 'Hormozgan', 'easy-digital-downloads' ),
		'SBN' => __( 'Sistan and Baluchestan', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_iranian_states', $states );
}

/**
 * Get Italian Provinces
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_italian_states_list() {
	$states = array(
		''   => '',
		'AG' => __( 'Agrigento', 'easy-digital-downloads' ),
		'AL' => __( 'Alessandria', 'easy-digital-downloads' ),
		'AN' => __( 'Ancona', 'easy-digital-downloads' ),
		'AO' => __( 'Aosta', 'easy-digital-downloads' ),
		'AR' => __( 'Arezzo', 'easy-digital-downloads' ),
		'AP' => __( 'Ascoli Piceno', 'easy-digital-downloads' ),
		'AT' => __( 'Asti', 'easy-digital-downloads' ),
		'AV' => __( 'Avellino', 'easy-digital-downloads' ),
		'BA' => __( 'Bari', 'easy-digital-downloads' ),
		'BT' => __( 'Barletta-Andria-Trani', 'easy-digital-downloads' ),
		'BL' => __( 'Belluno', 'easy-digital-downloads' ),
		'BN' => __( 'Benevento', 'easy-digital-downloads' ),
		'BG' => __( 'Bergamo', 'easy-digital-downloads' ),
		'BI' => __( 'Biella', 'easy-digital-downloads' ),
		'BO' => __( 'Bologna', 'easy-digital-downloads' ),
		'BZ' => __( 'Bolzano', 'easy-digital-downloads' ),
		'BS' => __( 'Brescia', 'easy-digital-downloads' ),
		'BR' => __( 'Brindisi', 'easy-digital-downloads' ),
		'CA' => __( 'Cagliari', 'easy-digital-downloads' ),
		'CL' => __( 'Caltanissetta', 'easy-digital-downloads' ),
		'CB' => __( 'Campobasso', 'easy-digital-downloads' ),
		'CI' => __( 'Caltanissetta', 'easy-digital-downloads' ),
		'CE' => __( 'Caserta', 'easy-digital-downloads' ),
		'CT' => __( 'Catania', 'easy-digital-downloads' ),
		'CZ' => __( 'Catanzaro', 'easy-digital-downloads' ),
		'CH' => __( 'Chieti', 'easy-digital-downloads' ),
		'CO' => __( 'Como', 'easy-digital-downloads' ),
		'CS' => __( 'Cosenza', 'easy-digital-downloads' ),
		'CR' => __( 'Cremona', 'easy-digital-downloads' ),
		'KR' => __( 'Crotone', 'easy-digital-downloads' ),
		'CN' => __( 'Cuneo', 'easy-digital-downloads' ),
		'EN' => __( 'Enna', 'easy-digital-downloads' ),
		'FM' => __( 'Fermo', 'easy-digital-downloads' ),
		'FE' => __( 'Ferrara', 'easy-digital-downloads' ),
		'FI' => __( 'Firenze', 'easy-digital-downloads' ),
		'FG' => __( 'Foggia', 'easy-digital-downloads' ),
		'FC' => __( 'Forli-Cesena', 'easy-digital-downloads' ),
		'FR' => __( 'Frosinone', 'easy-digital-downloads' ),
		'GE' => __( 'Genova', 'easy-digital-downloads' ),
		'GO' => __( 'Gorizia', 'easy-digital-downloads' ),
		'GR' => __( 'Grosseto', 'easy-digital-downloads' ),
		'IM' => __( 'Imperia', 'easy-digital-downloads' ),
		'IS' => __( 'Isernia', 'easy-digital-downloads' ),
		'SP' => __( 'La Spezia', 'easy-digital-downloads' ),
		'AQ' => __( 'L&apos;Aquila', 'easy-digital-downloads' ),
		'LT' => __( 'Latina', 'easy-digital-downloads' ),
		'LE' => __( 'Lecce', 'easy-digital-downloads' ),
		'LC' => __( 'Lecco', 'easy-digital-downloads' ),
		'LI' => __( 'Livorno', 'easy-digital-downloads' ),
		'LO' => __( 'Lodi', 'easy-digital-downloads' ),
		'LU' => __( 'Lucca', 'easy-digital-downloads' ),
		'MC' => __( 'Macerata', 'easy-digital-downloads' ),
		'MN' => __( 'Mantova', 'easy-digital-downloads' ),
		'MS' => __( 'Massa-Carrara', 'easy-digital-downloads' ),
		'MT' => __( 'Matera', 'easy-digital-downloads' ),
		'ME' => __( 'Messina', 'easy-digital-downloads' ),
		'MI' => __( 'Milano', 'easy-digital-downloads' ),
		'MO' => __( 'Modena', 'easy-digital-downloads' ),
		'MB' => __( 'Monza e della Brianza', 'easy-digital-downloads' ),
		'NA' => __( 'Napoli', 'easy-digital-downloads' ),
		'NO' => __( 'Novara', 'easy-digital-downloads' ),
		'NU' => __( 'Nuoro', 'easy-digital-downloads' ),
		'OT' => __( 'Olbia-Tempio', 'easy-digital-downloads' ),
		'OR' => __( 'Oristano', 'easy-digital-downloads' ),
		'PD' => __( 'Padova', 'easy-digital-downloads' ),
		'PA' => __( 'Palermo', 'easy-digital-downloads' ),
		'PR' => __( 'Parma', 'easy-digital-downloads' ),
		'PV' => __( 'Pavia', 'easy-digital-downloads' ),
		'PG' => __( 'Perugia', 'easy-digital-downloads' ),
		'PU' => __( 'Pesaro e Urbino', 'easy-digital-downloads' ),
		'PE' => __( 'Pescara', 'easy-digital-downloads' ),
		'PC' => __( 'Piacenza', 'easy-digital-downloads' ),
		'PI' => __( 'Pisa', 'easy-digital-downloads' ),
		'PT' => __( 'Pistoia', 'easy-digital-downloads' ),
		'PN' => __( 'Pordenone', 'easy-digital-downloads' ),
		'PZ' => __( 'Potenza', 'easy-digital-downloads' ),
		'PO' => __( 'Prato', 'easy-digital-downloads' ),
		'RG' => __( 'Ragusa', 'easy-digital-downloads' ),
		'RA' => __( 'Ravenna', 'easy-digital-downloads' ),
		'RC' => __( 'Reggio Calabria', 'easy-digital-downloads' ),
		'RE' => __( 'Reggio Emilia', 'easy-digital-downloads' ),
		'RI' => __( 'Rieti', 'easy-digital-downloads' ),
		'RN' => __( 'Rimini', 'easy-digital-downloads' ),
		'RM' => __( 'Roma', 'easy-digital-downloads' ),
		'RO' => __( 'Rovigo', 'easy-digital-downloads' ),
		'SA' => __( 'Salerno', 'easy-digital-downloads' ),
		'VS' => __( 'Medio Campidano', 'easy-digital-downloads' ),
		'SS' => __( 'Sassari', 'easy-digital-downloads' ),
		'SV' => __( 'Savona', 'easy-digital-downloads' ),
		'SI' => __( 'Siena', 'easy-digital-downloads' ),
		'SR' => __( 'Siracusa', 'easy-digital-downloads' ),
		'SO' => __( 'Sondrio', 'easy-digital-downloads' ),
		'TA' => __( 'Taranto', 'easy-digital-downloads' ),
		'TE' => __( 'Teramo', 'easy-digital-downloads' ),
		'TR' => __( 'Terni', 'easy-digital-downloads' ),
		'TO' => __( 'Torino', 'easy-digital-downloads' ),
		'OG' => __( 'Ogliastra', 'easy-digital-downloads' ),
		'TP' => __( 'Trapani', 'easy-digital-downloads' ),
		'TN' => __( 'Trento', 'easy-digital-downloads' ),
		'TV' => __( 'Treviso', 'easy-digital-downloads' ),
		'TS' => __( 'Trieste', 'easy-digital-downloads' ),
		'UD' => __( 'Udine', 'easy-digital-downloads' ),
		'VA' => __( 'Varesa', 'easy-digital-downloads' ),
		'VE' => __( 'Venezia', 'easy-digital-downloads' ),
		'VB' => __( 'Verbano-Cusio-Ossola', 'easy-digital-downloads' ),
		'VC' => __( 'Vercelli', 'easy-digital-downloads' ),
		'VR' => __( 'Verona', 'easy-digital-downloads' ),
		'VV' => __( 'Vibo Valentia', 'easy-digital-downloads' ),
		'VI' => __( 'Vicenza', 'easy-digital-downloads' ),
		'VT' => __( 'Viterbo', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_italian_states', $states );
}

/**
 * Get Malaysian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_malaysian_states_list() {
	$states = array(
		''    => '',
		'JHR' => __( 'Johor', 'easy-digital-downloads' ),
		'KDH' => __( 'Kedah', 'easy-digital-downloads' ),
		'KTN' => __( 'Kelantan', 'easy-digital-downloads' ),
		'MLK' => __( 'Melaka', 'easy-digital-downloads' ),
		'NSN' => __( 'Negeri Sembilan', 'easy-digital-downloads' ),
		'PHG' => __( 'Pahang', 'easy-digital-downloads' ),
		'PRK' => __( 'Perak', 'easy-digital-downloads' ),
		'PLS' => __( 'Perlis', 'easy-digital-downloads' ),
		'PNG' => __( 'Pulau Pinang', 'easy-digital-downloads' ),
		'SBH' => __( 'Sabah', 'easy-digital-downloads' ),
		'SWK' => __( 'Sarawak', 'easy-digital-downloads' ),
		'SGR' => __( 'Selangor', 'easy-digital-downloads' ),
		'TRG' => __( 'Terengganu', 'easy-digital-downloads' ),
		'KUL' => __( 'W.P. Kuala Lumpur', 'easy-digital-downloads' ),
		'LBN' => __( 'W.P. Labuan', 'easy-digital-downloads' ),
		'PJY' => __( 'W.P. Putrajaya', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_malaysian_states', $states );
}

/**
 * Get Mexican States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_mexican_states_list() {
	$states = array(
		''    => '',
		'DIF' => __( 'Distrito Federal', 'easy-digital-downloads' ),
		'JAL' => __( 'Jalisco', 'easy-digital-downloads' ),
		'NLE' => __( 'Nuevo Le&oacute;n', 'easy-digital-downloads' ),
		'AGU' => __( 'Aguascalientes', 'easy-digital-downloads' ),
		'BCN' => __( 'Baja California Norte', 'easy-digital-downloads' ),
		'BCS' => __( 'Baja California Sur', 'easy-digital-downloads' ),
		'CAM' => __( 'Campeche', 'easy-digital-downloads' ),
		'CHP' => __( 'Chiapas', 'easy-digital-downloads' ),
		'CHH' => __( 'Chihuahua', 'easy-digital-downloads' ),
		'COA' => __( 'Coahuila', 'easy-digital-downloads' ),
		'COL' => __( 'Colima', 'easy-digital-downloads' ),
		'DUR' => __( 'Durango', 'easy-digital-downloads' ),
		'GUA' => __( 'Guanajuato', 'easy-digital-downloads' ),
		'GRO' => __( 'Guerrero', 'easy-digital-downloads' ),
		'HID' => __( 'Hidalgo', 'easy-digital-downloads' ),
		'MEX' => __( 'Edo. de M&eacute;xico', 'easy-digital-downloads' ),
		'MIC' => __( 'Michoac&aacute;n', 'easy-digital-downloads' ),
		'MOR' => __( 'Morelos', 'easy-digital-downloads' ),
		'NAY' => __( 'Nayarit', 'easy-digital-downloads' ),
		'OAX' => __( 'Oaxaca', 'easy-digital-downloads' ),
		'PUE' => __( 'Puebla', 'easy-digital-downloads' ),
		'QUE' => __( 'Quer&eacute;taro', 'easy-digital-downloads' ),
		'ROO' => __( 'Quintana Roo', 'easy-digital-downloads' ),
		'SLP' => __( 'San Luis Potos&iacute;', 'easy-digital-downloads' ),
		'SIN' => __( 'Sinaloa', 'easy-digital-downloads' ),
		'SON' => __( 'Sonora', 'easy-digital-downloads' ),
		'TAB' => __( 'Tabasco', 'easy-digital-downloads' ),
		'TAM' => __( 'Tamaulipas', 'easy-digital-downloads' ),
		'TLA' => __( 'Tlaxcala', 'easy-digital-downloads' ),
		'VER' => __( 'Veracruz', 'easy-digital-downloads' ),
		'YUC' => __( 'Yucat&aacute;n', 'easy-digital-downloads' ),
		'ZAC' => __( 'Zacatecas', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_mexican_states', $states );
}

/**
 * Get Nepalese States (Districts)
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_nepalese_states_list() {
	$states = array(
		''    => '',
		'ILL' => __( 'Illam', 'easy-digital-downloads' ),
		'JHA' => __( 'Jhapa', 'easy-digital-downloads' ),
		'PAN' => __( 'Panchthar', 'easy-digital-downloads' ),
		'TAP' => __( 'Taplejung', 'easy-digital-downloads' ),
		'BHO' => __( 'Bhojpur', 'easy-digital-downloads' ),
		'DKA' => __( 'Dhankuta', 'easy-digital-downloads' ),
		'MOR' => __( 'Morang', 'easy-digital-downloads' ),
		'SUN' => __( 'Sunsari', 'easy-digital-downloads' ),
		'SAN' => __( 'Sankhuwa', 'easy-digital-downloads' ),
		'TER' => __( 'Terhathum', 'easy-digital-downloads' ),
		'KHO' => __( 'Khotang', 'easy-digital-downloads' ),
		'OKH' => __( 'Okhaldhunga', 'easy-digital-downloads' ),
		'SAP' => __( 'Saptari', 'easy-digital-downloads' ),
		'SIR' => __( 'Siraha', 'easy-digital-downloads' ),
		'SOL' => __( 'Solukhumbu', 'easy-digital-downloads' ),
		'UDA' => __( 'Udayapur', 'easy-digital-downloads' ),
		'DHA' => __( 'Dhanusa', 'easy-digital-downloads' ),
		'DLK' => __( 'Dolakha', 'easy-digital-downloads' ),
		'MOH' => __( 'Mohottari', 'easy-digital-downloads' ),
		'RAM' => __( 'Ramechha', 'easy-digital-downloads' ),
		'SAR' => __( 'Sarlahi', 'easy-digital-downloads' ),
		'SIN' => __( 'Sindhuli', 'easy-digital-downloads' ),
		'BHA' => __( 'Bhaktapur', 'easy-digital-downloads' ),
		'DHD' => __( 'Dhading', 'easy-digital-downloads' ),
		'KTM' => __( 'Kathmandu', 'easy-digital-downloads' ),
		'KAV' => __( 'Kavrepalanchowk', 'easy-digital-downloads' ),
		'LAL' => __( 'Lalitpur', 'easy-digital-downloads' ),
		'NUW' => __( 'Nuwakot', 'easy-digital-downloads' ),
		'RAS' => __( 'Rasuwa', 'easy-digital-downloads' ),
		'SPC' => __( 'Sindhupalchowk', 'easy-digital-downloads' ),
		'BAR' => __( 'Bara', 'easy-digital-downloads' ),
		'CHI' => __( 'Chitwan', 'easy-digital-downloads' ),
		'MAK' => __( 'Makwanpur', 'easy-digital-downloads' ),
		'PAR' => __( 'Parsa', 'easy-digital-downloads' ),
		'RAU' => __( 'Rautahat', 'easy-digital-downloads' ),
		'GOR' => __( 'Gorkha', 'easy-digital-downloads' ),
		'KAS' => __( 'Kaski', 'easy-digital-downloads' ),
		'LAM' => __( 'Lamjung', 'easy-digital-downloads' ),
		'MAN' => __( 'Manang', 'easy-digital-downloads' ),
		'SYN' => __( 'Syangja', 'easy-digital-downloads' ),
		'TAN' => __( 'Tanahun', 'easy-digital-downloads' ),
		'BAG' => __( 'Baglung', 'easy-digital-downloads' ),
		'PBT' => __( 'Parbat', 'easy-digital-downloads' ),
		'MUS' => __( 'Mustang', 'easy-digital-downloads' ),
		'MYG' => __( 'Myagdi', 'easy-digital-downloads' ),
		'AGR' => __( 'Agrghakanchi', 'easy-digital-downloads' ),
		'GUL' => __( 'Gulmi', 'easy-digital-downloads' ),
		'KAP' => __( 'Kapilbastu', 'easy-digital-downloads' ),
		'NAW' => __( 'Nawalparasi', 'easy-digital-downloads' ),
		'PAL' => __( 'Palpa', 'easy-digital-downloads' ),
		'RUP' => __( 'Rupandehi', 'easy-digital-downloads' ),
		'DAN' => __( 'Dang', 'easy-digital-downloads' ),
		'PYU' => __( 'Pyuthan', 'easy-digital-downloads' ),
		'ROL' => __( 'Rolpa', 'easy-digital-downloads' ),
		'RUK' => __( 'Rukum', 'easy-digital-downloads' ),
		'SAL' => __( 'Salyan', 'easy-digital-downloads' ),
		'BAN' => __( 'Banke', 'easy-digital-downloads' ),
		'BDA' => __( 'Bardiya', 'easy-digital-downloads' ),
		'DAI' => __( 'Dailekh', 'easy-digital-downloads' ),
		'JAJ' => __( 'Jajarkot', 'easy-digital-downloads' ),
		'SUR' => __( 'Surkhet', 'easy-digital-downloads' ),
		'DOL' => __( 'Dolpa', 'easy-digital-downloads' ),
		'HUM' => __( 'Humla', 'easy-digital-downloads' ),
		'JUM' => __( 'Jumla', 'easy-digital-downloads' ),
		'KAL' => __( 'Kalikot', 'easy-digital-downloads' ),
		'MUG' => __( 'Mugu', 'easy-digital-downloads' ),
		'ACH' => __( 'Achham', 'easy-digital-downloads' ),
		'BJH' => __( 'Bajhang', 'easy-digital-downloads' ),
		'BJU' => __( 'Bajura', 'easy-digital-downloads' ),
		'DOT' => __( 'Doti', 'easy-digital-downloads' ),
		'KAI' => __( 'Kailali', 'easy-digital-downloads' ),
		'BAI' => __( 'Baitadi', 'easy-digital-downloads' ),
		'DAD' => __( 'Dadeldhura', 'easy-digital-downloads' ),
		'DAR' => __( 'Darchula', 'easy-digital-downloads' ),
		'KAN' => __( 'Kanchanpur', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_nepalese_states', $states );
}

/**
 * Get South African States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_south_african_states_list() {
	$states = array(
		''    => '',
		'EC'  => __( 'Eastern Cape', 'easy-digital-downloads' ),
		'FS'  => __( 'Free State', 'easy-digital-downloads' ),
		'GP'  => __( 'Gauteng', 'easy-digital-downloads' ),
		'KZN' => __( 'KwaZulu-Natal', 'easy-digital-downloads' ),
		'LP'  => __( 'Limpopo', 'easy-digital-downloads' ),
		'MP'  => __( 'Mpumalanga', 'easy-digital-downloads' ),
		'NC'  => __( 'Northern Cape', 'easy-digital-downloads' ),
		'NW'  => __( 'North West', 'easy-digital-downloads' ),
		'WC'  => __( 'Western Cape', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_south_african_states', $states );
}

/**
 * Get Thailand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_thailand_states_list() {
	$states = array(
		''      => '',
		'TH-37' => __( 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)', 'easy-digital-downloads' ),
		'TH-15' => __( 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)', 'easy-digital-downloads' ),
		'TH-14' => __( 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)', 'easy-digital-downloads' ),
		'TH-10' => __( 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)', 'easy-digital-downloads' ),
		'TH-38' => __( 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)', 'easy-digital-downloads' ),
		'TH-31' => __( 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)', 'easy-digital-downloads' ),
		'TH-24' => __( 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)', 'easy-digital-downloads' ),
		'TH-18' => __( 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)', 'easy-digital-downloads' ),
		'TH-36' => __( 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)', 'easy-digital-downloads' ),
		'TH-22' => __( 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-50' => __( 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)', 'easy-digital-downloads' ),
		'TH-57' => __( 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)', 'easy-digital-downloads' ),
		'TH-20' => __( 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-86' => __( 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)', 'easy-digital-downloads' ),
		'TH-46' => __( 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)', 'easy-digital-downloads' ),
		'TH-62' => __( 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)', 'easy-digital-downloads' ),
		'TH-71' => __( 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-40' => __( 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)', 'easy-digital-downloads' ),
		'TH-81' => __( 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)', 'easy-digital-downloads' ),
		'TH-52' => __( 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)', 'easy-digital-downloads' ),
		'TH-51' => __( 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)', 'easy-digital-downloads' ),
		'TH-42' => __( 'Loei (&#3648;&#3621;&#3618;)', 'easy-digital-downloads' ),
		'TH-16' => __( 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-58' => __( 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)', 'easy-digital-downloads' ),
		'TH-44' => __( 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)', 'easy-digital-downloads' ),
		'TH-49' => __( 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)', 'easy-digital-downloads' ),
		'TH-26' => __( 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)', 'easy-digital-downloads' ),
		'TH-73' => __( 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)', 'easy-digital-downloads' ),
		'TH-48' => __( 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)', 'easy-digital-downloads' ),
		'TH-30' => __( 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)', 'easy-digital-downloads' ),
		'TH-60' => __( 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)', 'easy-digital-downloads' ),
		'TH-80' => __( 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)', 'easy-digital-downloads' ),
		'TH-55' => __( 'Nan (&#3609;&#3656;&#3634;&#3609;)', 'easy-digital-downloads' ),
		'TH-96' => __( 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)', 'easy-digital-downloads' ),
		'TH-39' => __( 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)', 'easy-digital-downloads' ),
		'TH-43' => __( 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)', 'easy-digital-downloads' ),
		'TH-12' => __( 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-13' => __( 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-94' => __( 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-82' => __( 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)', 'easy-digital-downloads' ),
		'TH-93' => __( 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)', 'easy-digital-downloads' ),
		'TH-56' => __( 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)', 'easy-digital-downloads' ),
		'TH-67' => __( 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)', 'easy-digital-downloads' ),
		'TH-76' => __( 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-66' => __( 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)', 'easy-digital-downloads' ),
		'TH-65' => __( 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)', 'easy-digital-downloads' ),
		'TH-54' => __( 'Phrae (&#3649;&#3614;&#3619;&#3656;)', 'easy-digital-downloads' ),
		'TH-83' => __( 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)', 'easy-digital-downloads' ),
		'TH-25' => __( 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-77' => __( 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)', 'easy-digital-downloads' ),
		'TH-85' => __( 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)', 'easy-digital-downloads' ),
		'TH-70' => __( 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-21' => __( 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)', 'easy-digital-downloads' ),
		'TH-45' => __( 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)', 'easy-digital-downloads' ),
		'TH-27' => __( 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)', 'easy-digital-downloads' ),
		'TH-47' => __( 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)', 'easy-digital-downloads' ),
		'TH-11' => __( 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)', 'easy-digital-downloads' ),
		'TH-74' => __( 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)', 'easy-digital-downloads' ),
		'TH-75' => __( 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)', 'easy-digital-downloads' ),
		'TH-19' => __( 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-91' => __( 'Satun (&#3626;&#3605;&#3641;&#3621;)', 'easy-digital-downloads' ),
		'TH-17' => __( 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-33' => __( 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)', 'easy-digital-downloads' ),
		'TH-90' => __( 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)', 'easy-digital-downloads' ),
		'TH-64' => __( 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)', 'easy-digital-downloads' ),
		'TH-72' => __( 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)', 'easy-digital-downloads' ),
		'TH-84' => __( 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-32' => __( 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)', 'easy-digital-downloads' ),
		'TH-63' => __( 'Tak (&#3605;&#3634;&#3585;)', 'easy-digital-downloads' ),
		'TH-92' => __( 'Trang (&#3605;&#3619;&#3633;&#3591;)', 'easy-digital-downloads' ),
		'TH-23' => __( 'Trat (&#3605;&#3619;&#3634;&#3604;)', 'easy-digital-downloads' ),
		'TH-34' => __( 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-41' => __( 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-61' => __( 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)', 'easy-digital-downloads' ),
		'TH-53' => __( 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)', 'easy-digital-downloads' ),
		'TH-95' => __( 'Yala (&#3618;&#3632;&#3621;&#3634;)', 'easy-digital-downloads' ),
		'TH-35' => __( 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_thailand_states', $states );
}

/**
 * Get Turkey States
 *
 * @since 2.2.3
 * @return array $states A list of states
 */
function edd_get_turkey_states_list() {
	$states = array(
		''     => '',
		'TR01' => __( 'Adana', 'easy-digital-downloads' ),
		'TR02' => __( 'Ad&#305;yaman', 'easy-digital-downloads' ),
		'TR03' => __( 'Afyon', 'easy-digital-downloads' ),
		'TR04' => __( 'A&#287;r&#305;', 'easy-digital-downloads' ),
		'TR05' => __( 'Amasya', 'easy-digital-downloads' ),
		'TR06' => __( 'Ankara', 'easy-digital-downloads' ),
		'TR07' => __( 'Antalya', 'easy-digital-downloads' ),
		'TR08' => __( 'Artvin', 'easy-digital-downloads' ),
		'TR09' => __( 'Ayd&#305;n', 'easy-digital-downloads' ),
		'TR10' => __( 'Bal&#305;kesir', 'easy-digital-downloads' ),
		'TR11' => __( 'Bilecik', 'easy-digital-downloads' ),
		'TR12' => __( 'Bing&#246;l', 'easy-digital-downloads' ),
		'TR13' => __( 'Bitlis', 'easy-digital-downloads' ),
		'TR14' => __( 'Bolu', 'easy-digital-downloads' ),
		'TR15' => __( 'Burdur', 'easy-digital-downloads' ),
		'TR16' => __( 'Bursa', 'easy-digital-downloads' ),
		'TR17' => __( '&#199;anakkale', 'easy-digital-downloads' ),
		'TR18' => __( '&#199;ank&#305;kesir', 'easy-digital-downloads' ),
		'TR19' => __( '&#199;orum', 'easy-digital-downloads' ),
		'TR20' => __( 'Denizli', 'easy-digital-downloads' ),
		'TR21' => __( 'Diyarbak&#305;r', 'easy-digital-downloads' ),
		'TR22' => __( 'Edirne', 'easy-digital-downloads' ),
		'TR23' => __( 'Elaz&#305;&#287;', 'easy-digital-downloads' ),
		'TR24' => __( 'Erzincan', 'easy-digital-downloads' ),
		'TR25' => __( 'Erzurum', 'easy-digital-downloads' ),
		'TR26' => __( 'Eski&#351;ehir', 'easy-digital-downloads' ),
		'TR27' => __( 'Gaziantep', 'easy-digital-downloads' ),
		'TR28' => __( 'Giresun', 'easy-digital-downloads' ),
		'TR29' => __( 'G&#252;m&#252;&#351;hane', 'easy-digital-downloads' ),
		'TR30' => __( 'Hakkari', 'easy-digital-downloads' ),
		'TR31' => __( 'Hatay', 'easy-digital-downloads' ),
		'TR32' => __( 'Isparta', 'easy-digital-downloads' ),
		'TR33' => __( '&#304;&#231;el', 'easy-digital-downloads' ),
		'TR34' => __( '&#304;stanbul', 'easy-digital-downloads' ),
		'TR35' => __( '&#304;zmir', 'easy-digital-downloads' ),
		'TR36' => __( 'Kars', 'easy-digital-downloads' ),
		'TR37' => __( 'Kastamonu', 'easy-digital-downloads' ),
		'TR38' => __( 'Kayseri', 'easy-digital-downloads' ),
		'TR39' => __( 'K&#305;rklareli', 'easy-digital-downloads' ),
		'TR40' => __( 'K&#305;r&#351;ehir', 'easy-digital-downloads' ),
		'TR41' => __( 'Kocaeli', 'easy-digital-downloads' ),
		'TR42' => __( 'Konya', 'easy-digital-downloads' ),
		'TR43' => __( 'K&#252;tahya', 'easy-digital-downloads' ),
		'TR44' => __( 'Malatya', 'easy-digital-downloads' ),
		'TR45' => __( 'Manisa', 'easy-digital-downloads' ),
		'TR46' => __( 'Kahramanmara&#351;', 'easy-digital-downloads' ),
		'TR47' => __( 'Mardin', 'easy-digital-downloads' ),
		'TR48' => __( 'Mu&#287;la', 'easy-digital-downloads' ),
		'TR49' => __( 'Mu&#351;', 'easy-digital-downloads' ),
		'TR50' => __( 'Nev&#351;ehir', 'easy-digital-downloads' ),
		'TR51' => __( 'Ni&#287;de', 'easy-digital-downloads' ),
		'TR52' => __( 'Ordu', 'easy-digital-downloads' ),
		'TR53' => __( 'Rize', 'easy-digital-downloads' ),
		'TR54' => __( 'Sakarya', 'easy-digital-downloads' ),
		'TR55' => __( 'Samsun', 'easy-digital-downloads' ),
		'TR56' => __( 'Siirt', 'easy-digital-downloads' ),
		'TR57' => __( 'Sinop', 'easy-digital-downloads' ),
		'TR58' => __( 'Sivas', 'easy-digital-downloads' ),
		'TR59' => __( 'Tekirda&#287;', 'easy-digital-downloads' ),
		'TR60' => __( 'Tokat', 'easy-digital-downloads' ),
		'TR61' => __( 'Trabzon', 'easy-digital-downloads' ),
		'TR62' => __( 'Tunceli', 'easy-digital-downloads' ),
		'TR63' => __( '&#350;anl&#305;urfa', 'easy-digital-downloads' ),
		'TR64' => __( 'U&#351;ak', 'easy-digital-downloads' ),
		'TR65' => __( 'Van', 'easy-digital-downloads' ),
		'TR66' => __( 'Yozgat', 'easy-digital-downloads' ),
		'TR67' => __( 'Zonguldak', 'easy-digital-downloads' ),
		'TR68' => __( 'Aksaray', 'easy-digital-downloads' ),
		'TR69' => __( 'Bayburt', 'easy-digital-downloads' ),
		'TR70' => __( 'Karaman', 'easy-digital-downloads' ),
		'TR71' => __( 'K&#305;r&#305;kkale', 'easy-digital-downloads' ),
		'TR72' => __( 'Batman', 'easy-digital-downloads' ),
		'TR73' => __( '&#350;&#305;rnak', 'easy-digital-downloads' ),
		'TR74' => __( 'Bart&#305;n', 'easy-digital-downloads' ),
		'TR75' => __( 'Ardahan', 'easy-digital-downloads' ),
		'TR76' => __( 'I&#287;d&#305;r', 'easy-digital-downloads' ),
		'TR77' => __( 'Yalova', 'easy-digital-downloads' ),
		'TR78' => __( 'Karab&#252;k', 'easy-digital-downloads' ),
		'TR79' => __( 'Kilis', 'easy-digital-downloads' ),
		'TR80' => __( 'Osmaniye', 'easy-digital-downloads' ),
		'TR81' => __( 'D&#252;zce', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_turkey_states', $states );
}

/**
 * Get Spain States
 *
 * @since 2.2
 * @return array $states A list of states
 */
function edd_get_spain_states_list() {
	$states = array(
		''   => '',
	    'C'  => __( 'A Coru&ntilde;a', 'easy-digital-downloads' ),
	    'VI' => __( 'Araba', 'easy-digital-downloads' ),
	    'AB' => __( 'Albacete', 'easy-digital-downloads' ),
	    'A'  => __( 'Alicante', 'easy-digital-downloads' ),
	    'AL' => __( 'Almer&iacute;a', 'easy-digital-downloads' ),
	    'O'  => __( 'Asturias', 'easy-digital-downloads' ),
	    'AV' => __( '&Aacute;vila', 'easy-digital-downloads' ),
	    'BA' => __( 'Badajoz', 'easy-digital-downloads' ),
	    'PM' => __( 'Baleares', 'easy-digital-downloads' ),
	    'B'  => __( 'Barcelona', 'easy-digital-downloads' ),
	    'BU' => __( 'Burgos', 'easy-digital-downloads' ),
	    'CC' => __( 'C&aacute;ceres', 'easy-digital-downloads' ),
	    'CA' => __( 'C&aacute;diz', 'easy-digital-downloads' ),
	    'S'  => __( 'Cantabria', 'easy-digital-downloads' ),
	    'CS' => __( 'Castell&oacute;n', 'easy-digital-downloads' ),
	    'CE' => __( 'Ceuta', 'easy-digital-downloads' ),
	    'CR' => __( 'Ciudad Real', 'easy-digital-downloads' ),
	    'CO' => __( 'C&oacute;rdoba', 'easy-digital-downloads' ),
	    'CU' => __( 'Cuenca', 'easy-digital-downloads' ),
	    'GI' => __( 'Girona', 'easy-digital-downloads' ),
	    'GR' => __( 'Granada', 'easy-digital-downloads' ),
	    'GU' => __( 'Guadalajara', 'easy-digital-downloads' ),
	    'SS' => __( 'Gipuzkoa', 'easy-digital-downloads' ),
	    'H'  => __( 'Huelva', 'easy-digital-downloads' ),
	    'HU' => __( 'Huesca', 'easy-digital-downloads' ),
	    'J'  => __( 'Ja&eacute;n', 'easy-digital-downloads' ),
	    'LO' => __( 'La Rioja', 'easy-digital-downloads' ),
	    'GC' => __( 'Las Palmas', 'easy-digital-downloads' ),
	    'LE' => __( 'Le&oacute;n', 'easy-digital-downloads' ),
	    'L'  => __( 'Lleida', 'easy-digital-downloads' ),
	    'LU' => __( 'Lugo', 'easy-digital-downloads' ),
	    'M'  => __( 'Madrid', 'easy-digital-downloads' ),
	    'MA' => __( 'M&aacute;laga', 'easy-digital-downloads' ),
	    'ML' => __( 'Melilla', 'easy-digital-downloads' ),
	    'MU' => __( 'Murcia', 'easy-digital-downloads' ),
	    'NA' => __( 'Navarra', 'easy-digital-downloads' ),
	    'OR' => __( 'Ourense', 'easy-digital-downloads' ),
	    'P'  => __( 'Palencia', 'easy-digital-downloads' ),
	    'PO' => __( 'Pontevedra', 'easy-digital-downloads' ),
	    'SA' => __( 'Salamanca', 'easy-digital-downloads' ),
	    'TF' => __( 'Santa Cruz de Tenerife', 'easy-digital-downloads' ),
	    'SG' => __( 'Segovia', 'easy-digital-downloads' ),
	    'SE' => __( 'Sevilla', 'easy-digital-downloads' ),
	    'SO' => __( 'Soria', 'easy-digital-downloads' ),
	    'T'  => __( 'Tarragona', 'easy-digital-downloads' ),
	    'TE' => __( 'Teruel', 'easy-digital-downloads' ),
	    'TO' => __( 'Toledo', 'easy-digital-downloads' ),
	    'V'  => __( 'Valencia', 'easy-digital-downloads' ),
	    'VA' => __( 'Valladolid', 'easy-digital-downloads' ),
	    'BI' => __( 'Bizkaia', 'easy-digital-downloads' ),
	    'ZA' => __( 'Zamora', 'easy-digital-downloads' ),
	    'Z'  => __( 'Zaragoza', 'easy-digital-downloads' ),
	);

	return apply_filters( 'edd_spain_states', $states );
}
