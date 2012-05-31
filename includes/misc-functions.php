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


/**
 * Is Test Mode
 *
 * @access      public
 * @since       1.0 
 * @return      boolean
*/

function edd_is_test_mode() {
	global $edd_options;
	if(isset($edd_options['test_mode']))
		return true;
	return false;
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
	if(isset($edd_options['logged_in_only']))
		return true;
	return false;
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
	if(isset($edd_options['logged_in_only']))
		return true;
	return false;
}


/**
 * Disable Redownload
 *
 * @access      public
 * @since       1.08.2
 * @return      boolean
*/

function edd_no_redownload() {
	global $edd_options;
	if(isset($edd_options['disable_redownload']))
		return true;
	return false;	
}

/**
 * Get Menu Access Level 
 *
 * Returns the access level required to access 
 * the downloads menu. Currently not not changeable,
 * but here for a future update.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_menu_access_level() {
	global $edd_options;
	return 'manage_options';
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

function edd_get_file_extension($str)
{
   $parts = explode('.', $str);
   return end($parts);
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

function edd_get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
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
		'TRY' => __('Turkish Lira', 'edd')
	);
	return apply_filters('edd_currencies', $currencies);
}


/**
 * Get Currency Filter 
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_currency_filter( $price ) {
	global $edd_options;
	$currency = isset($edd_options['currency']) ? $edd_options['currency'] : 'USD';
	$position = isset($edd_options['currency_position']) ? $edd_options['currency_position'] : 'before';
	if($position == 'before') :
		switch ($currency) :
			case "GBP" : return '&pound;' . $price; break;
			case "USD" : 
			case "AUD" : 
			case "BRL" : 
			case "CAD" : 
			case "HKD" : 
			case "MXN" : 
			case "SGD" : 
				return '&#36;' . $price; 
			break;
			case "JPY" : return '&yen;' . $price; break;
			default :
			    $formatted = $currency . ' ' . $price;
    		    return apply_filters('edd_' . strtolower($currency) . '_currency_filter_before', $formatted, $currency, $price);
			break;
		endswitch;
	else :
		switch ($currency) :
			case "GBP" : return $price . '&pound;'; break;
			case "USD" : 
			case "AUD" : 
			case "BRL" : 
			case "CAD" : 
			case "HKD" : 
			case "MXN" : 
			case "SGD" : 
				return $price . '&#36;'; 
			break;
			case "JPY" : return $price . '&yen;'; break;
			default : 
			    $formatted = $price . ' ' . $currency;
			    return apply_filters('edd_' . strtolower($currency) . '_currency_filter_after', $formatted, $currency, $price);
			break;
		endswitch;	
	endif;
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
		'GB' => 'United Kingdom (GB)',
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
		'CC' => 'Cocos (keeling) Islands',
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
		'CS' => 'Serbia and Montenegro',
		'CU' => 'Cuba',
		'CV' => 'Cap Verde',
		'CS' => 'Christmas Island',
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
		'FK' => 'Falkland Islands (Malvina)',
		'FM' => 'Micronesia, Federal State of',
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
		'IR' => 'Iran (Islamic Republic of)',
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
		'KP' => 'Korea, Democratic People\'s Republic',
		'KR' => 'Korea, Republic of',
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
	return $countries;
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

function edd_month_num_to_name($n)
{
    $timestamp = mktime(0, 0, 0, $n, 1, 2005);
    
    return date("M", $timestamp);
}