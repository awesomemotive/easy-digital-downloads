<?php
/**
 * Country Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin Williamson
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
	global $edd_options;
	$country = isset( $edd_options['base_country'] ) ? $edd_options['base_country'] : 'US';
	return apply_filters( 'edd_shop_country', $country );
}

/**
 * Get Shop Base State
 *
 * @since 1.6
 * @return string $state The shop's base state name
 */
function edd_get_shop_state() {
	global $edd_options;
	$state = isset( $edd_options['base_state'] ) ? $edd_options['base_state'] : false;
	return apply_filters( 'edd_shop_state', $state );
}

/**
 * Get Shop States
 *
 * @since 1.6
 *
 * @param null $country
 * @return mixed|void  A list of states for the shop's base country
 */
function edd_get_shop_states( $country = null ) {
	global $edd_options;

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
		case 'MY' :
			$states = edd_get_malaysian_states_list();
			break;
		case 'NZ' :
			$states = edd_get_new_zealand_states_list();
			break;
		case 'TH' :
			$states = edd_get_thailand_states_list();
			break;
		case 'ZA' :
			$states = edd_get_south_african_states_list();
			break;
		default :
			$states = array();
			break;

	endswitch;

	return apply_filters( 'edd_shop_states', $states );
}


/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries
 */
function edd_get_country_list() {
	$countries = array(
		'0'  => __( 'Choose', 'edd' ),
		'US' => 'United States',
		'CA' => 'Canada',
		'GB' => 'United Kingdom',
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darrussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CD' => 'Congo, Democratic People\'s Republic',
		'CG' => 'Congo, Republic of',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote d\'Ivoire',
		'HR' => 'Croatia/Hrvatska',
		'CU' => 'Cuba',
		'CY' => 'Cyprus Island',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TP' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'GQ' => 'Equatorial Guinea',
		'SV' => 'El Salvador',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard and McDonald Islands',
		'VA' => 'Holy See (City Vatican State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macau',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'Mv' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KR' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territories',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Phillipines',
		'PN' => 'Pitcairn Island',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion Island',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovak Republic',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia',
		'KP' => 'South Korea',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen Islands',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TH' => 'Thailand',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'UY' => 'Uruguay',
		'UM' => 'US Minor Outlying Islands',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (USA)',
		'WF' => 'Wallis and Futuna Islands',
		'EH' => 'Western Sahara',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'YU' => 'Yugoslavia',
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
	$states = array(
		'0'  => __( 'Choose', 'edd' ),
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
		'NE' => 'Nebraska',
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
		'0'  => __( 'Choose', 'edd' ),
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
 * Get Australian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_australian_states_list() {
	$states = array(
		'0'   => __( 'Choose', 'edd' ),
		'ACT' => 'Australian Capital Territory',
		'NSW' => 'New South Wales',
		'NT'  => 'Northern Territory',
		'QLD' => 'Queensland',
		'SA'  => 'South Australia',
		'TAS' => 'Tasmania',
		'VIC' => 'Victoria',
		'WA'  => 'Western Australia'
	);

	return apply_filters( 'edd_australian_states', $states );
}

/**
 * Get Brazil States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_brazil_states_list() {
	$states = array(
		'0'  => __( 'Choose', 'edd' ),
		'AC' => 'Acre',
		'AL' => 'Alagoas',
		'AP' => 'Amap&aacute;',
		'AM' => 'Amazonas',
		'BA' => 'Bahia',
		'CE' => 'Cear&aacute;',
		'DF' => 'Distrito Federal',
		'ES' => 'Esp&iacute;rito Santo',
		'GO' => 'Goi&aacute;s',
		'MA' => 'Maranh&atilde;o',
		'MT' => 'Mato Grosso',
		'MS' => 'Mato Grosso do Sul',
		'MG' => 'Minas Gerais',
		'PA' => 'Par&aacute;',
		'PB' => 'Para&iacute;ba',
		'PR' => 'Paran&aacute;',
		'PE' => 'Pernambuco',
		'PI' => 'Piau&iacute;',
		'RJ' => 'Rio de Janeiro',
		'RN' => 'Rio Grande do Norte',
		'RS' => 'Rio Grande do Sul',
		'RO' => 'Rond&ocirc;nia',
		'RR' => 'Roraima',
		'SC' => 'Santa Catarina',
		'SP' => 'S&atilde;o Paulo',
		'SE' => 'Sergipe',
		'TO' => 'Tocantins'
	);

	return apply_filters( 'edd_brazil_states', $states );
}

/**
 * Get Hong Kong States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_hong_kong_states_list() {
	$states = array(
		'0'               => __( 'Choose', 'edd' ),
		'HONG KONG'       => 'Hong Kong Island',
		'KOWLOON'         => 'Kowloon',
		'NEW TERRITORIES' => 'New Territories'
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
		'0'  => __( 'Choose', 'edd' ),
		'BK' => 'Bács-Kiskun',
		'BE' => 'Békés',
		'BA' => 'Baranya',
		'BZ' => 'Borsod-Abaúj-Zemplén',
		'BU' => 'Budapest',
		'CS' => 'Csongrád',
		'FE' => 'Fejér',
		'GS' => 'Győr-Moson-Sopron',
		'HB' => 'Hajdú-Bihar',
		'HE' => 'Heves',
		'JN' => 'Jász-Nagykun-Szolnok',
		'KE' => 'Komárom-Esztergom',
		'NO' => 'Nógrád',
		'PE' => 'Pest',
		'SO' => 'Somogy',
		'SZ' => 'Szabolcs-Szatmár-Bereg',
		'TO' => 'Tolna',
		'VA' => 'Vas',
		'VE' => 'Veszprém',
		'ZA' => 'Zala'
	);

	return apply_filters( 'edd_hungary_states', $states );
}

/**
 * Get Chinese States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_chinese_states_list() {
	$states = array(
		'0'    => __( 'Choose', 'edd' ),
	    'CN1'  => 'Yunnan / &#20113;&#21335;',
	    'CN2'  => 'Beijing / &#21271;&#20140;',
	    'CN3'  => 'Tianjin / &#22825;&#27941;',
	    'CN4'  => 'Hebei / &#27827;&#21271;',
	    'CN5'  => 'Shanxi / &#23665;&#35199;',
	    'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
	    'CN7'  => 'Liaoning / &#36797;&#23425;',
	    'CN8'  => 'Jilin / &#21513;&#26519;',
	    'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
	    'CN10' => 'Shanghai / &#19978;&#28023;',
	    'CN11' => 'Jiangsu / &#27743;&#33487;',
	    'CN12' => 'Zhejiang / &#27993;&#27743;',
	    'CN13' => 'Anhui / &#23433;&#24509;',
	    'CN14' => 'Fujian / &#31119;&#24314;',
	    'CN15' => 'Jiangxi / &#27743;&#35199;',
	    'CN16' => 'Shandong / &#23665;&#19996;',
	    'CN17' => 'Henan / &#27827;&#21335;',
	    'CN18' => 'Hubei / &#28246;&#21271;',
	    'CN19' => 'Hunan / &#28246;&#21335;',
	    'CN20' => 'Guangdong / &#24191;&#19996;',
	    'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
	    'CN22' => 'Hainan / &#28023;&#21335;',
	    'CN23' => 'Chongqing / &#37325;&#24198;',
	    'CN24' => 'Sichuan / &#22235;&#24029;',
	    'CN25' => 'Guizhou / &#36149;&#24030;',
	    'CN26' => 'Shaanxi / &#38485;&#35199;',
	    'CN27' => 'Gansu / &#29976;&#32899;',
	    'CN28' => 'Qinghai / &#38738;&#28023;',
	    'CN29' => 'Ningxia Hui / &#23425;&#22799;',
	    'CN30' => 'Macau / &#28595;&#38376;',
	    'CN31' => 'Tibet / &#35199;&#34255;',
	    'CN32' => 'Xinjiang / &#26032;&#30086;'
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
		'0'  => __( 'Choose', 'edd' ),
		'AK' => 'Auckland',
		'BP' => 'Bay of Plenty',
		'CT' => 'Canterbury',
		'HB' => 'Hawke&rsquo;s Bay',
		'MW' => 'Manawatu-Wanganui',
		'MB' => 'Marlborough',
		'NS' => 'Nelson',
		'NL' => 'Northland',
		'OT' => 'Otago',
		'SL' => 'Southland',
		'TK' => 'Taranaki',
		'TM' => 'Tasman',
		'WA' => 'Waikato',
		'WE' => 'Wellington',
		'WC' => 'West Coast'
	);

	return apply_filters( 'edd_new_zealand_states', $states );
}

/**
 * Get Indonesian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_indonesian_states_list() {
	$states  = array(
		'0'  => __( 'Choose', 'edd' ),
		'AC' => 'Daerah Istimewa Aceh',
	    'SU' => 'Sumatera Utara',
	    'SB' => 'Sumatera Barat',
	    'RI' => 'Riau',
	    'KR' => 'Kepulauan Riau',
	    'JA' => 'Jambi',
	    'SS' => 'Sumatera Selatan',
	    'BB' => 'Bangka Belitung',
	    'BE' => 'Bengkulu',
	    'LA' => 'Lampung',
	    'JK' => 'DKI Jakarta',
	    'JB' => 'Jawa Barat',
	    'BT' => 'Banten',
	    'JT' => 'Jawa Tengah',
	    'JI' => 'Jawa Timur',
	    'YO' => 'Daerah Istimewa Yogyakarta',
	    'BA' => 'Bali',
	    'NB' => 'Nusa Tenggara Barat',
	    'NT' => 'Nusa Tenggara Timur',
	    'KB' => 'Kalimantan Barat',
	    'KT' => 'Kalimantan Tengah',
	    'KI' => 'Kalimantan Timur',
	    'KS' => 'Kalimantan Selatan',
	    'KU' => 'Kalimantan Utara',
	    'SA' => 'Sulawesi Utara',
	    'ST' => 'Sulawesi Tengah',
	    'SG' => 'Sulawesi Tenggara',
	    'SR' => 'Sulawesi Barat',
	    'SN' => 'Sulawesi Selatan',
	    'GO' => 'Gorontalo',
	    'MA' => 'Maluku',
	    'MU' => 'Maluku Utara',
	    'PA' => 'Papua',
	    'PB' => 'Papua Barat'
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
		'0'  => __( 'Choose', 'edd' ),
		'AP' => 'Andra Pradesh',
		'AR' => 'Arunachal Pradesh',
		'AS' => 'Assam',
		'BR' => 'Bihar',
		'CT' => 'Chhattisgarh',
		'GA' => 'Goa',
		'GJ' => 'Gujarat',
		'HR' => 'Haryana',
		'HP' => 'Himachal Pradesh',
		'JK' => 'Jammu and Kashmir',
		'JH' => 'Jharkhand',
		'KA' => 'Karnataka',
		'KL' => 'Kerala',
		'MP' => 'Madhya Pradesh',
		'MH' => 'Maharashtra',
		'MN' => 'Manipur',
		'ML' => 'Meghalaya',
		'MZ' => 'Mizoram',
		'NL' => 'Nagaland',
		'OR' => 'Orissa',
		'PB' => 'Punjab',
		'RJ' => 'Rajasthan',
		'SK' => 'Sikkim',
		'TN' => 'Tamil Nadu',
		'TR' => 'Tripura',
		'UT' => 'Uttaranchal',
		'UP' => 'Uttar Pradesh',
		'WB' => 'West Bengal',
		'AN' => 'Andaman and Nicobar Islands',
		'CH' => 'Chandigarh',
		'DN' => 'Dadar and Nagar Haveli',
		'DD' => 'Daman and Diu',
		'DL' => 'Delhi',
		'LD' => 'Lakshadeep',
		'PY' => 'Pondicherry (Puducherry)'
	);

	return apply_filters( 'edd_indian_states', $states );
}

/**
 * Get Malaysian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_malaysian_states_list() {
	$states = array(
		'0'   => __( 'Choose', 'edd' ),
		'JHR' => 'Johor',
		'KDH' => 'Kedah',
		'KTN' => 'Kelantan',
		'MLK' => 'Melaka',
		'NSN' => 'Negeri Sembilan',
		'PHG' => 'Pahang',
		'PRK' => 'Perak',
		'PLS' => 'Perlis',
		'PNG' => 'Pulau Pinang',
		'SBH' => 'Sabah',
		'SWK' => 'Sarawak',
		'SGR' => 'Selangor',
		'TRG' => 'Terengganu',
		'KUL' => 'W.P. Kuala Lumpur',
		'LBN' => 'W.P. Labuan',
		'PJY' => 'W.P. Putrajaya'
	);

	return apply_filters( 'edd_malaysian_states', $states );
}

/**
 * Get South African States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function edd_get_south_african_states_list() {
	$states = array(
		'0'   => __( 'Choose', 'edd' ),
		'EC'  => 'Eastern Cape',
		'FS'  => 'Free State',
		'GP'  => 'Gauteng',
		'KZN' => 'KwaZulu-Natal',
		'LP'  => 'Limpopo',
		'MP'  => 'Mpumalanga',
		'NC'  => 'Northern Cape',
		'NW'  => 'North West',
		'WC'  => 'Western Cape'
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
		'0'     => __( 'Choose', 'edd' ),
		'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
		'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
		'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
		'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
		'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
		'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
		'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
		'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
		'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
		'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
		'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
		'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
		'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
		'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
		'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
		'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
		'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
		'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
		'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
		'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
		'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
		'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
		'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
		'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
		'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
		'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
		'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
		'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
		'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
		'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
		'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
		'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
		'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
		'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
		'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
		'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
		'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
		'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
		'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
		'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
		'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
		'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
		'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
		'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
		'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
		'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
		'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
		'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
		'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
		'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
		'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
		'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
		'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
		'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
		'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
		'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
		'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
		'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
		'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
		'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
		'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
		'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
		'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
		'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
		'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
		'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
		'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
		'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
		'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
		'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
		'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
		'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
		'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
	);

	return apply_filters( 'edd_thailand_states', $states );
}
