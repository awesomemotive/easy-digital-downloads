<?php

function edd_is_test_mode() {
	global $edd_options;
	if(isset($edd_options['test_mode']))
		return true;
	return false;
}

function edd_logged_in_only() {
	global $edd_options;
	if(isset($edd_options['logged_in_only']))
		return true;
	return false;
}

// checks whether an integer is odd
function edd_is_odd( $int ) {
	return( $int & 1 );
}

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
		'THB' => __('Thai Baht', 'edd')
	);
	return $currencies;
}

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
			default : return $currency . ' ' . $price; break;
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
			default : return $price . ' ' . $currency; break;
		endswitch;	
	endif;
}

function edd_get_country_list() {
	$countries = array(
		"" =>"Select Country",
		"AF"=>"AFGHANISTAN", 
		"AX"=>"ALAND ISLANDS", 
		"AL"=>"ALBANIA", 
		"DZ"=>"ALGERIA", 
		"AS"=>"AMERICAN SAMOA", 
		"AD"=>"ANDORRA", 
		"AO"=>"ANGOLA", 
		"AI"=>"ANGUILLA", 
		"AQ"=>"ANTARCTICA", 
		"AG"=>"ANTIGUA AND BARBUDA", 
		"AR"=>"ARGENTINA", 
		"AM"=>"ARMENIA", 
		"AW"=>"ARUBA", 
		"AU"=>"AUSTRALIA", 
		"AT"=>"AUSTRIA", 
		"AZ"=>"AZERBAIJAN", 
		"BS"=>"BAHAMAS", 
		"BH"=>"BAHRAIN", 
		"BD"=>"BANGLADESH", 
		"BB"=>"BARBADOS", 
		"BY"=>"BELARUS", 
		"BE"=>"BELGIUM", 
		"BZ"=>"BELIZE", 
		"BJ"=>"BENIN", 
		"BM"=>"BERMUDA", 
		"BT"=>"BHUTAN", 
		"BO"=>"BOLIVIA", 
		"BA"=>"BOSNIA AND HERZEGOVINA", 
		"BW"=>"BOTSWANA", 
		"BV"=>"BOUVET ISLAND", 
		"BR"=>"BRAZIL", 
		"IO"=>"BRITISH INDIAN OCEAN TERRITORY", 
		"BN"=>"BRUNEI DARUSSALAM", 
		"BG"=>"BULGARIA", 
		"BF"=>"BURKINA FASO", 
		"BI"=>"BURUNDI", 
		"KH"=>"CAMBODIA", 
		"CM"=>"CAMEROON", 
		"CA"=>"CANADA", 
		"CV"=>"CAPE VERDE", 
		"CI"=>"CâTE D'IVOIRE", 
		"KY"=>"CAYMAN ISLANDS", 
		"CF"=>"CENTRAL AFRICAN REPUBLIC", 
		"TD"=>"CHAD", 
		"CL"=>"CHILE", 
		"CN"=>"CHINA", 
		"CX"=>"CHRISTMAS ISLAND", 
		"CC"=>"COCOS (KEELING) ISLANDS", 
		"CO"=>"COLOMBIA", 
		"KM"=>"COMOROS", 
		"CG"=>"CONGO", 
		"CD"=>"CONGO, THE DEMOCRATIC REPUBLIC OF THE", 
		"CK"=>"COOK ISLANDS", 
		"CR"=>"COSTA RICA", 
		"HR"=>"CROATIA", 
		"CU"=>"CUBA", 
		"CY"=>"CYPRUS", 
		"CZ"=>"CZECH REPUBLIC", 
		"DK"=>"DENMARK", 
		"DJ"=>"DJIBOUTI", 
		"DM"=>"DOMINICA", 
		"DO"=>"DOMINICAN REPUBLIC", 
		"EC"=>"ECUADOR", 
		"EG"=>"EGYPT", 
		"SV"=>"EL SALVADOR", 
		"GQ"=>"EQUATORIAL GUINEA", 
		"ER"=>"ERITREA", 
		"EE"=>"ESTONIA", 
		"ET"=>"ETHIOPIA", 
		"FK"=>"FALKLAND ISLANDS (MALVINAS)", 
		"FO"=>"FAROE ISLANDS", 
		"FJ"=>"FIJI", 
		"FI"=>"FINLAND", 
		"FR"=>"FRANCE", 
		"GF"=>"FRENCH GUIANA", 
		"PF"=>"FRENCH POLYNESIA", 
		"TF"=>"FRENCH SOUTHERN TERRITORIES", 
		"GA"=>"GABON", 
		"GM"=>"GAMBIA", 
		"GE"=>"GEORGIA", 
		"DE"=>"GERMANY", 
		"GH"=>"GHANA", 
		"GI"=>"GIBRALTAR", 
		"GR"=>"GREECE", 
		"GL"=>"GREENLAND", 
		"GD"=>"GRENADA", 
		"GP"=>"GUADELOUPE", 
		"GU"=>"GUAM", 
		"GT"=>"GUATEMALA", 
		"GN"=>"GUINEA", 
		"GW"=>"GUINEA-BISSAU", 
		"GY"=>"GUYANA", 
		"HT"=>"HAITI", 
		"HM"=>"HEARD ISLAND AND MCDONALD ISLANDS", 
		"VA"=>"HOLY SEE (VATICAN CITY STATE)", 
		"HN"=>"HONDURAS", 
		"HK"=>"HONG KONG", 
		"HU"=>"HUNGARY", 
		"IS"=>"ICELAND", 
		"IN"=>"INDIA", 
		"ID"=>"INDONESIA", 
		"IR"=>"IRAN ISLAMIC REPUBLIC OF", 
		"IQ"=>"IRAQ", 
		"IE"=>"IRELAND", 
		"IL"=>"ISRAEL", 
		"IT"=>"ITALY", 
		"JM"=>"JAMAICA", 
		"JP"=>"JAPAN", 
		"JO"=>"JORDAN", 
		"KZ"=>"KAZAKHSTAN", 
		"KE"=>"KENYA", 
		"KI"=>"KIRIBATI", 
		"KP"=>"KOREA DEMOCRATIC PEOPLE\'S REPUBLIC OF", 
		"KR"=>"KOREA REPUBLIC OF", 
		"KW"=>"KUWAIT", 
		"KG"=>"KYRGYZSTAN", 
		"LA"=>"LAO PEOPLE\'S DEMOCRATIC REPUBLIC", 
		"LV"=>"LATVIA", 
		"LB"=>"LEBANON", 
		"LS"=>"LESOTHO", 
		"LR"=>"LIBERIA", 
		"LY"=>"LIBYAN ARAB JAMAHIRIYA", 
		"LI"=>"LIECHTENSTEIN", 
		"LT"=>"LITHUANIA", 
		"LU"=>"LUXEMBOURG", 
		"MO"=>"MACAO", 
		"MK"=>"MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", 
		"MG"=>"MADAGASCAR", 
		"MW"=>"MALAWI", 
		"MY"=>"MALAYSIA", 
		"MV"=>"MALDIVES", 
		"ML"=>"MALI", 
		"MT"=>"MALTA", 
		"MH"=>"MARSHALL ISLANDS", 
		"MQ"=>"MARTINIQUE", 
		"MR"=>"MAURITANIA", 
		"MU"=>"MAURITIUS", 
		"YT"=>"MAYOTTE", 
		"MX"=>"MEXICO", 
		"FM"=>"MICRONESIA, FEDERATED STATES OF", 
		"MD"=>"MOLDOVA, REPUBLIC OF", 
		"MC"=>"MONACO", 
		"MN"=>"MONGOLIA", 
		"MS"=>"MONTSERRAT", 
		"MA"=>"MOROCCO", 
		"MZ"=>"MOZAMBIQUE", 
		"MM"=>"MYANMAR", 
		"NA"=>"NAMIBIA", 
		"NR"=>"NAURU", 
		"NP"=>"NEPAL", 
		"NL"=>"NETHERLANDS", 
		"AN"=>"NETHERLANDS ANTILLES", 
		"NC"=>"NEW CALEDONIA", 
		"NZ"=>"NEW ZEALAND", 
		"NI"=>"NICARAGUA", 
		"NE"=>"NIGER", 
		"NG"=>"NIGERIA", 
		"NU"=>"NIUE", 
		"NF"=>"NORFOLK ISLAND", 
		"MP"=>"NORTHERN MARIANA ISLANDS", 
		"NO"=>"NORWAY", 
		"OM"=>"OMAN", 
		"PK"=>"PAKISTAN", 
		"PW"=>"PALAU", 
		"PS"=>"PALESTINIAN TERRITORY, OCCUPIED", 
		"PA"=>"PANAMA", 
		"PG"=>"PAPUA NEW GUINEA", 
		"PY"=>"PARAGUAY", 
		"PE"=>"PERU", 
		"PH"=>"PHILIPPINES", 
		"PN"=>"PITCAIRN", 
		"PL"=>"POLAND", 
		"PT"=>"PORTUGAL", 
		"PR"=>"PUERTO RICO", 
		"QA"=>"QATAR", 
		"RE"=>"REUNION", 
		"RO"=>"ROMANIA", 
		"RU"=>"RUSSIAN FEDERATION", 
		"RW"=>"RWANDA", 
		"SH"=>"SAINT HELENA", 
		"KN"=>"SAINT KITTS AND NEVIS", 
		"LC"=>"SAINT LUCIA", 
		"PM"=>"SAINT PIERRE AND MIQUELON", 
		"VC"=>"SAINT VINCENT AND THE GRENADINES", 
		"WS"=>"SAMOA", 
		"SM"=>"SAN MARINO", 
		"ST"=>"SAO TOME AND PRINCIPE", 
		"SA"=>"SAUDI ARABIA", 
		"SN"=>"SENEGAL", 
		"CS"=>"SERBIA AND MONTENEGRO", 
		"SC"=>"SEYCHELLES", 
		"SL"=>"SIERRA LEONE", 
		"SG"=>"SINGAPORE", 
		"SK"=>"SLOVAKIA", 
		"SI"=>"SLOVENIA", 
		"SB"=>"SOLOMON ISLANDS", 
		"SO"=>"SOMALIA", 
		"ZA"=>"SOUTH AFRICA", 
		"GS"=>"SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", 
		"ES"=>"SPAIN", 
		"LK"=>"SRI LANKA", 
		"SD"=>"SUDAN", 
		"SR"=>"SURINAME", 
		"SJ"=>"SVALBARD AND JAN MAYEN", 
		"SZ"=>"SWAZILAND", 
		"SE"=>"SWEDEN", 
		"CH"=>"SWITZERLAND", 
		"SY"=>"SYRIAN ARAB REPUBLIC", 
		"TW"=>"TAIWAN PROVINCE OF CHINA", 
		"TJ"=>"TAJIKISTAN", 
		"TZ"=>"TANZANIA UNITED REPUBLIC OF", 
		"TH"=>"THAILAND", 
		"TL"=>"TIMOR-LESTE", 
		"TG"=>"TOGO", 
		"TK"=>"TOKELAU", 
		"TO"=>"TONGA", 
		"TT"=>"TRINIDAD AND TOBAGO", 
		"TN"=>"TUNISIA", 
		"TR"=>"TURKEY", 
		"TM"=>"TURKMENISTAN", 
		"TC"=>"TURKS AND CAICOS ISLANDS", 
		"TV"=>"TUVALU", 
		"UG"=>"UGANDA", 
		"UA"=>"UKRAINE", 
		"AE"=>"UNITED ARAB EMIRATES", 
		"GB"=>"UNITED KINGDOM", 
		"US"=>"UNITED STATES", 
		"UM"=>"UNITED STATES MINOR OUTLYING ISLANDS", 
		"UY"=>"URUGUAY", 
		"UZ"=>"UZBEKISTAN", 
		"VU"=>"VANUATU", 
		"VE"=>"VENEZUELA", 
		"VN"=>"VIETNAM", 
		"VG"=>"VIRGIN ISLANDS BRITISH", 
		"VI"=>"VIRGIN ISLANDS U.S.", 
		"WF"=>"WALLIS AND FUTUNA", 
		"EH"=>"WESTERN SAHARA", 
		"YE"=>"YEMEN", 
		"ZM"=>"ZAMBIA", 
		"ZW"=>"ZIMBABWE"
	);
	return $countries;
}

// takes a month number and returns the name three letter name of it
function edd_month_num_to_name($n)
{
    $timestamp = mktime(0, 0, 0, $n, 1, 2005);
    
    return date("M", $timestamp);
}