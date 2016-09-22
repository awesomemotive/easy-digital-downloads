<?php


/**
 * @group edd_misc
 */
class Test_Misc extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_test_mode() {
		$this->assertFalse( edd_is_test_mode() );
	}

	public function test_guest_checkout() {
		$this->assertFalse( edd_no_guest_checkout() );
	}

	public function test_logged_in_only() {
		$this->assertFalse( edd_logged_in_only() );
	}

	public function test_straight_to_checkout() {
		$this->assertFalse( edd_straight_to_checkout() );
	}

	public function test_no_redownload() {
		$this->assertFalse( edd_no_redownload() );
	}

	public function test_is_cc_verify_enabled() {
		$this->assertTrue( edd_is_cc_verify_enabled() );
	}

	public function test_is_odd() {
		$this->assertTrue( edd_is_odd( 3 ) );
		$this->assertFalse( edd_is_odd( 4 ) );
	}

	public function test_get_file_extension() {
		$this->assertEquals( 'php', edd_get_file_extension( 'file.php' ) );
	}

	public function test_string_is_image_url() {
		$this->assertTrue( edd_string_is_image_url( 'jpg' ) );
		$this->assertFalse( edd_string_is_image_url( 'php' ) );
	}

	public function test_get_ip() {
		$this->assertEquals( '127.0.0.1', edd_get_ip() );

		$_SERVER['REMOTE_ADDR'] = ' 192.168.0.1 , 192.168.1.1 ';
		$this->assertEquals( '192.168.0.1', edd_get_ip() );

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	public function test_get_currencies() {
		$expected = array(
			'USD'  => __( 'US Dollars (&#36;)', 'easy-digital-downloads' ),
			'EUR'  => __( 'Euros (&euro;)', 'easy-digital-downloads' ),
			'GBP'  => __( 'Pounds Sterling (&pound;)', 'easy-digital-downloads' ),
			'AUD'  => __( 'Australian Dollars (&#36;)', 'easy-digital-downloads' ),
			'BRL'  => __( 'Brazilian Real (R&#36;)', 'easy-digital-downloads' ),
			'CAD'  => __( 'Canadian Dollars (&#36;)', 'easy-digital-downloads' ),
			'CZK'  => __( 'Czech Koruna', 'easy-digital-downloads' ),
			'DKK'  => __( 'Danish Krone', 'easy-digital-downloads' ),
			'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'easy-digital-downloads' ),
			'HUF'  => __( 'Hungarian Forint', 'easy-digital-downloads' ),
			'ILS'  => __( 'Israeli Shekel (&#8362;)', 'easy-digital-downloads' ),
			'JPY'  => __( 'Japanese Yen (&yen;)', 'easy-digital-downloads' ),
			'MYR'  => __( 'Malaysian Ringgits', 'easy-digital-downloads' ),
			'MXN'  => __( 'Mexican Peso (&#36;)', 'easy-digital-downloads' ),
			'NZD'  => __( 'New Zealand Dollar (&#36;)', 'easy-digital-downloads' ),
			'NOK'  => __( 'Norwegian Krone', 'easy-digital-downloads' ),
			'PHP'  => __( 'Philippine Pesos', 'easy-digital-downloads' ),
			'PLN'  => __( 'Polish Zloty', 'easy-digital-downloads' ),
			'SGD'  => __( 'Singapore Dollar (&#36;)', 'easy-digital-downloads' ),
			'SEK'  => __( 'Swedish Krona', 'easy-digital-downloads' ),
			'CHF'  => __( 'Swiss Franc', 'easy-digital-downloads' ),
			'TWD'  => __( 'Taiwan New Dollars', 'easy-digital-downloads' ),
			'THB'  => __( 'Thai Baht (&#3647;)', 'easy-digital-downloads' ),
			'INR'  => __( 'Indian Rupee (&#8377;)', 'easy-digital-downloads' ),
			'TRY'  => __( 'Turkish Lira (&#8378;)', 'easy-digital-downloads' ),
			'RIAL' => __( 'Iranian Rial (&#65020;)', 'easy-digital-downloads' ),
			'RUB'  => __( 'Russian Rubles', 'easy-digital-downloads' )
		);

		$this->assertEquals( $expected, edd_get_currencies() );

	}

	public function test_get_countries() {
		$expected = array(
			''   => '',
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AF' => 'Afghanistan',
			'AX' => '&#197;land Islands',
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
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
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
			'CW' => 'Cura&Ccedil;ao',
			'CY' => 'Cyprus',
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
			'MV' => 'Maldives',
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
			'KP' => 'North Korea',
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
			'PH' => 'Philippines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'XK' => 'Republic of Kosovo',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barth&eacute;lemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French)',
			'SX' => 'Saint Martin (Dutch)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
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
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
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
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
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
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		$this->assertEquals( $expected, edd_get_country_list() );
	}

	public function test_states_list() {
		$expected = array(
			''   => '',
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
			'AP' => 'Armed Forces - Pacific',
		);

		$this->assertEquals( $expected, edd_get_states_list() );
	}

	public function test_provinces_list() {
		$expected = array(
			''   => '',
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
			'YT' => 'Yukon',
		);

		$this->assertEquals( $expected, edd_get_provinces_list() );
	}

	public function test_month_num_to_name() {
		$this->assertEquals( 'Jan', edd_month_num_to_name( 1 ) );
	}

	public function test_get_php_arg_separator_output() {
		$this->assertEquals( '&', edd_get_php_arg_separator_output() );
	}

	public function test_let_to_num() {
		$this->assertEquals( 0, edd_let_to_num( WP_MEMORY_LIMIT ) / ( 1024*1024 ) );
	}

	/**
	 * @covers ::edd_get_symlink_dir
	 */
	public function test_get_symlink_url() {
		$this->assertEquals( 'http://example.org/wp-content/uploads/edd/symlinks', edd_get_symlink_url() );
	}

	public function test_use_skus() {
		$this->assertFalse( edd_use_skus() );
	}

	public function test_edd_is_host() {
		$this->assertFalse( edd_is_host( 'wpengine' ) );
		$this->assertFalse( edd_is_host( 'wp engine' ) );
		$this->assertFalse( edd_is_host( 'WP Engine' ) );
		$this->assertFalse( edd_is_host( 'WPEngine' ) );

		define( 'WPE_APIKEY', 'testkey' );

		$this->assertTrue( edd_is_host( 'wpengine' ) );
		$this->assertTrue( edd_is_host( 'wp engine' ) );
		$this->assertTrue( edd_is_host( 'WP Engine' ) );
		$this->assertTrue( edd_is_host( 'WPEngine' ) );
	}

	public function test_edd_update_option(){
		$key   = 'some-setting';
		$value = 'some-value';
		$isset = edd_get_option( $key, false );

		// The option shouldn't exist
		$this->assertFalse( $isset );

		$updated = edd_update_option( $key, $value );

		// The option should have succesfully updated
		$this->assertTrue( $updated );

		// The option retrieve should be equal to the one we set
		$this->assertEquals( $value, edd_get_option( $key, false ) );

		$key   = 'some-setting2';
		$value = null;
		$isset = edd_get_option( $key, false );

		// The option shouldn't exist
		$this->assertFalse( $isset );

		$updated = edd_update_option( $key, $value );

		// The option should return false due to the null value
		$this->assertFalse( $updated );

		// The option retrieve should be false since it doesn't exist
		$this->assertFalse( edd_get_option( $key, false ) );

	}

	public function test_add_cache_busting() {
		add_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$this->assertEquals( 'http://example.org/?nocache=true', edd_add_cache_busting( home_url( '/') ) );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$this->assertEquals( 'http://example.org/', edd_add_cache_busting( home_url( '/' ) ) );
	}

	public function test_get_current_page_url() {
		global $edd_options;
		$this->go_to( home_url( '/' ) );
		$this->assertEquals( 'http://example.org/', edd_get_current_page_url() );

		$post = EDD_Helper_Download::create_simple_download();
		$this->go_to( get_permalink( $post->ID ) );
		$this->assertEquals( 'http://example.org/?download=test-download-product', edd_get_current_page_url() );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$this->go_to( get_permalink( $post->ID ) );
		$this->assertEquals( 'http://example.org/?download=test-download-product&nocache=true', edd_get_current_page_url( true ) );
		EDD_Helper_Download::delete_download( $post->ID );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );


		$checkout = edd_get_option( 'purchase_page', false );
		$this->go_to( get_permalink( $checkout ) );
		$this->assertEquals( edd_get_checkout_uri(), edd_get_current_page_url() );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );
		$edd_options['no_cache_checkout'] = true;
		$this->assertEquals( edd_get_checkout_uri(), edd_get_current_page_url( true ) );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );

	}

	public function test_cart_url_formats() {
		global $edd_options;
		$post = EDD_Helper_Download::create_simple_download();

		edd_add_to_cart( $post->ID );

		$this->assertTrue( edd_item_in_cart( $post->ID ) );

		$item_position = edd_get_item_position_in_cart( $post->ID );

		// Go to checkout
		$this->go_to( edd_get_checkout_uri() );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url = edd_remove_item_url( $item_position );

		$this->assertContains( 'page_id=3', $remove_url );
		$this->assertContains( 'edd_action=remove', $remove_url );
		$this->assertContains( 'nocache=true', $remove_url );
		$this->assertContains( 'cart_item=' . $item_position, $remove_url );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );
		unset( $edd_options['no_cache_checkout'] );
		$remove_url = edd_remove_item_url( $item_position );

		$this->assertContains( 'page_id=3', $remove_url );
		$this->assertContains( 'edd_action=remove', $remove_url );
		$this->assertContains( 'cart_item=' . $item_position, $remove_url );
		$this->assertNotContains( 'nocache=true', $remove_url );

		// Go home and test again
		$this->go_to( home_url( '/' ) );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$expected_url = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove&nocache=true';
		$remove_url   = edd_remove_item_url( $item_position );

		$this->assertNotContains( 'page_id=', $remove_url );
		$this->assertContains( 'edd_action=remove', $remove_url );
		$this->assertContains( 'cart_item=' . $item_position, $remove_url );
		$this->assertContains( 'nocache=true', $remove_url );

		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url = edd_remove_item_url( $item_position );

		$this->assertNotContains( 'page_id=', $remove_url );
		$this->assertContains( 'edd_action=remove', $remove_url );
		$this->assertContains( 'cart_item=' . $item_position, $remove_url );
		$this->assertNotContains( 'nocache=true', $remove_url );

		// Go home and test again
		$this->go_to( home_url( '/' ) );

		add_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$expected_url  = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove&nocache=true';
		$remove_url    = edd_remove_item_url( $item_position );

		$this->assertEquals( $expected_url, $remove_url );
		remove_filter( 'edd_is_caching_plugin_active', '__return_true' );

		$remove_url    = edd_remove_item_url( $item_position );
		$expected_url  = 'http://example.org/?cart_item=' . $item_position . '&edd_action=remove';

		EDD_Helper_Download::delete_download( $post->ID );
	}

	public function test_array_convert() {
		$customer1_id = EDD()->customers->add( array( 'email' => 'test10@example.com' ) );
		$customer2_id = EDD()->customers->add( array( 'email' => 'test11@example.com' ) );

		// Test sending a single object in
		$customer_object = new EDD_Customer( $customer1_id );
		$customer_array  = edd_object_to_array( $customer_object );
		$this->assertInternalType( 'array', $customer_array );
		$this->assertEquals( $customer_object->id, $customer_array['id'] );
		$this->assertEquals( $customer_object->email, $customer_array['email'] );
		$this->assertEquals( $customer_object->purchase_count, $customer_array['purchase_count'] );

		// Negative tests (no alterations should occur)
		$this->assertEquals( 'string', edd_object_to_array( 'string' ) );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), edd_object_to_array( array( 'foo', 'bar', 'baz' ) ) );

		// Test sending in an array of objects
		$customers = EDD()->customers->get_customers();
		$converted = edd_object_to_array( $customers );
		$this->assertInternalType( 'array', $converted[0] );

		// Test payments
		$payment_1 = EDD_Helper_Payment::create_simple_payment();
		$payment_2 = EDD_Helper_Payment::create_simple_payment();

		$payment_1_obj = new EDD_Payment( $payment_1 );
		$payment_2_obj = new EDD_Payment( $payment_2 );

		// Test a single convert
		$payment_1_array = edd_object_to_array( $payment_1_obj );
		$this->assertInternalType( 'array',  $payment_1_array );
		$this->assertEquals( $payment_1_obj->ID, $payment_1_array['ID'] );

		$payments = array(
			$payment_1_obj,
			$payment_2_obj,
		);

		$payments_array = edd_object_to_array( $payments );
		$this->assertInternalType( 'array', $payments_array[0] );
		$this->assertEquals( 2, count( $payments_array ) );
	}
}
