<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \Aelia\CurrencySwitcher\Logger as Logger;

/**
 * Allows to select a Currency based on a geographic region.
 */
class WC_Aelia_Currencies_Manager {
	// @var array A list of all world currencies. This list will be populated in
	// the world_currencies() method
	protected static $_world_currencies;

	// @var array A list of the Currencies used by all Countries
	protected $_country_currencies = array(
		'AD' => 'EUR', // Andorra - Euro
		'AE' => 'AED', // United Arab Emirates - Arab Emirates Dirham
		'AF' => 'AFA', // Afghanistan - Afghanistan Afghani
		'AG' => 'XCD', // Antigua and Barbuda - East Caribbean Dollar
		'AI' => 'XCD', // Anguilla - East Caribbean Dollar
		'AL' => 'ALL', // Albania - Albanian Lek
		'AM' => 'AMD', // Armenia - Armenian Dram
		'AN' => 'ANG', // Netherlands Antilles - Netherlands Antillean Guilder
		'AO' => 'AOA', // Angola - Angolan Kwanza
		'AQ' => 'ATA', // Antarctica - Dollar
		'AR' => 'ARS', // Argentina - Argentine Peso
		'AS' => 'USD', // American Samoa - US Dollar
		'AT' => 'EUR', // Austria - Euro
		'AU' => 'AUD', // Australia - Australian Dollar
		'AW' => 'AWG', // Aruba - Aruban Florin
		'AX' => 'EUR', // Aland Islands - Euro
		'AZ' => 'AZN', // Azerbaijan - Azerbaijani Manat
		'BA' => 'BAM', // Bosnia-Herzegovina - Marka
		'BB' => 'BBD', // Barbados - Barbados Dollar
		'BD' => 'BDT', // Bangladesh - Bangladeshi Taka
		'BE' => 'EUR', // Belgium - Euro
		'BF' => 'XOF', // Burkina Faso - CFA Franc BCEAO
		'BG' => 'BGN', // Bulgaria - Bulgarian Lev
		'BH' => 'BHD', // Bahrain - Bahraini Dinar
		'BI' => 'BIF', // Burundi - Burundi Franc
		'BJ' => 'XOF', // Benin - CFA Franc BCEAO
		'BL' => 'EUR', // Saint Barthelemy - Euro
		'BM' => 'BMD', // Bermuda - Bermudian Dollar
		'BN' => 'BND', // Brunei Darussalam - Brunei Dollar
		'BO' => 'BOB', // Bolivia - Boliviano
		'BQ' => 'USD', // Bonaire, Sint Eustatius and Saba - US Dollar
		'BR' => 'BRL', // Brazil - Brazilian Real
		'BS' => 'BSD', // Bahamas - Bahamian Dollar
		'BT' => 'BTN', // Bhutan - Bhutan Ngultrum
		'BV' => 'NOK', // Bouvet Island - Norwegian Krone
		'BW' => 'BWP', // Botswana - Botswana Pula
		'BY' => 'BYR', // Belarus - Belarussian Ruble
		'BZ' => 'BZD', // Belize - Belize Dollar
		'CA' => 'CAD', // Canada - Canadian Dollar
		'CC' => 'AUD', // Cocos (Keeling) Islands - Australian Dollar
		'CD' => 'CDF', // Democratic Republic of Congo - Francs
		'CF' => 'XAF', // Central African Republic - CFA Franc BEAC
		'CG' => 'XAF', // Republic of the Congo - CFA Franc BEAC
		'CH' => 'CHF', // Switzerland - Swiss Franc
		'CI' => 'XOF', // Ivory Coast - CFA Franc BCEAO
		'CK' => 'NZD', // Cook Islands - New Zealand Dollar
		'CL' => 'CLP', // Chile - Chilean Peso
		'CM' => 'XAF', // Cameroon - CFA Franc BEAC
		'CN' => 'CNY', // China - Yuan Renminbi
		'CO' => 'COP', // Colombia - Colombian Peso
		'CR' => 'CRC', // Costa Rica - Costa Rican Colon
		'CU' => 'CUP', // Cuba - Cuban Peso
		'CV' => 'CVE', // Cape Verde - Cape Verde Escudo
		'CW' => 'ANG', // Curacao - Netherlands Antillean Guilder
		'CX' => 'AUD', // Christmas Island - Australian Dollar
		'CY' => 'EUR', // Cyprus - Euro
		'CZ' => 'CZK', // Czech Rep. - Czech Koruna
		'DE' => 'EUR', // Germany - Euro
		'DJ' => 'DJF', // Djibouti - Djibouti Franc
		'DK' => 'DKK', // Denmark - Danish Krone
		'DM' => 'XCD', // Dominica - East Caribbean Dollar
		'DO' => 'DOP', // Dominican Republic - Dominican Peso
		'DZ' => 'DZD', // Algeria - Algerian Dinar
		'EC' => 'ECS', // Ecuador - Ecuador Sucre
		'EE' => 'EUR', // Estonia - Euro
		'EG' => 'EGP', // Egypt - Egyptian Pound
		'EH' => 'MAD', // Western Sahara - Moroccan Dirham
		'ER' => 'ERN', // Eritrea - Eritrean Nakfa
		'ES' => 'EUR', // Spain - Euro
		'ET' => 'ETB', // Ethiopia - Ethiopian Birr
		'FI' => 'EUR', // Finland - Euro
		'FJ' => 'FJD', // Fiji - Fiji Dollar
		'FK' => 'FKP', // Falkland Islands - Falkland Islands Pound
		'FM' => 'USD', // Micronesia - US Dollar
		'FO' => 'DKK', // Faroe Islands - Danish Krone
		'FR' => 'EUR', // France - Euro
		'GA' => 'XAF', // Gabon - CFA Franc BEAC
		'GB' => 'GBP', // United Kingdom - Pound Sterling
		'GD' => 'XCD', // Grenada - East Carribean Dollar
		'GE' => 'GEL', // Georgia - Georgian Lari
		'GF' => 'EUR', // French Guiana - Euro
		'GG' => 'GBP', // Guernsey - Pound Sterling
		'GH' => 'GHS', // Ghana - Ghanaian Cedi
		'GI' => 'GIP', // Gibraltar - Gibraltar Pound
		'GL' => 'DKK', // Greenland - Danish Krone
		'GM' => 'GMD', // Gambia - Gambian Dalasi
		'GN' => 'GNF', // Guinea - Guinea Franc
		'GP' => 'EUR', // Guadeloupe (French) - Euro
		'GQ' => 'XAF', // Equatorial Guinea - CFA Franc BEAC
		'GR' => 'EUR', // Greece - Euro
		'GS' => 'GBP', // South Georgia & South Sandwich Islands - Pound Sterling
		'GT' => 'GTQ', // Guatemala - Guatemalan Quetzal
		'GU' => 'USD', // Guam (USA) - US Dollar
		'GW' => 'XAF', // Guinea Bissau - CFA Franc BEAC
		'GY' => 'GYD', // Guyana - Guyana Dollar
		'HK' => 'HKD', // Hong Kong - Hong Kong Dollar
		'HM' => 'AUD', // Heard Island and McDonald Islands - Australian Dollar
		'HN' => 'HNL', // Honduras - Honduran Lempira
		'HR' => 'HRK', // Croatia - Croatian Kuna
		'HT' => 'HTG', // Haiti - Haitian Gourde
		'HU' => 'HUF', // Hungary - Hungarian Forint
		'ID' => 'IDR', // Indonesia - Indonesian Rupiah
		'IE' => 'EUR', // Ireland - Euro
		'IL' => 'ILS', // Israel - Israeli New Shekel
		'IM' => 'GBP', // Isle of Man - Pound Sterling
		'IN' => 'INR', // India - Indian Rupee
		'IO' => 'USD', // British Indian Ocean Territory - US Dollar
		'IQ' => 'IQD', // Iraq - Iraqi Dinar
		'IR' => 'IRR', // Iran - Iranian Rial
		'IS' => 'ISK', // Iceland - Iceland Krona
		'IT' => 'EUR', // Italy - Euro
		'JE' => 'GBP', // Jersey - Pound Sterling
		'JM' => 'JMD', // Jamaica - Jamaican Dollar
		'JO' => 'JOD', // Jordan - Jordanian Dinar
		'JP' => 'JPY', // Japan - Japanese Yen
		'KE' => 'KES', // Kenya - Kenyan Shilling
		'KG' => 'KGS', // Kyrgyzstan - Som
		'KH' => 'KHR', // Cambodia - Kampuchean Riel
		'KI' => 'AUD', // Kiribati - Australian Dollar
		'KM' => 'KMF', // Comoros - Comoros Franc
		'KN' => 'XCD', // Saint Kitts & Nevis Anguilla - East Caribbean Dollar
		'KP' => 'KPW', // Korea, North - North Korean Won
		'KR' => 'KRW', // Korea, South - Korean Won
		'KW' => 'KWD', // Kuwait - Kuwaiti Dinar
		'KY' => 'KYD', // Cayman Islands - Cayman Islands Dollar
		'KZ' => 'KZT', // Kazakhstan - Kazakhstan Tenge
		'LA' => 'LAK', // Laos - Lao Kip
		'LB' => 'LBP', // Lebanon - Lebanese Pound
		'LC' => 'XCD', // Saint Lucia - East Caribbean Dollar
		'LI' => 'CHF', // Liechtenstein - Swiss Franc
		'LK' => 'LKR', // Sri Lanka - Sri Lanka Rupee
		'LR' => 'LRD', // Liberia - Liberian Dollar
		'LS' => 'LSL', // Lesotho - Lesotho Loti
		'LT' => 'LTL', // Lithuania - Lithuanian Litas
		'LU' => 'EUR', // Luxembourg - Euro
		'LV' => 'LVL', // Latvia - Latvian Lats
		'LY' => 'LYD', // Libya - Libyan Dinar
		'MA' => 'MAD', // Morocco - Moroccan Dirham
		'MC' => 'EUR', // Monaco - Euro
		'MD' => 'MDL', // Moldova - Moldovan Leu
		'ME' => 'EUR', // Montenegro - Euro
		'MF' => 'EUR', // Saint Martin (French Part) - Euro
		'MG' => 'MGA', // Madagascar - Malagasy Ariary
		'MH' => 'USD', // Marshall Islands - US Dollar
		'MK' => 'MKD', // Macedonia - Denar
		'ML' => 'XOF', // Mali - CFA Franc BCEAO
		'MM' => 'MMK', // Myanmar - Myanmar Kyat
		'MN' => 'MNT', // Mongolia - Mongolian Tugrik
		'MO' => 'MOP', // Macau - Macau Pataca
		'MP' => 'USD', // Northern Mariana Islands - US Dollar
		'MQ' => 'EUR', // Martinique (French) - Euro
		'MR' => 'MRO', // Mauritania - Mauritanian Ouguiya
		'MS' => 'XCD', // Montserrat - East Caribbean Dollar
		'MT' => 'EUR', // Malta - Euro
		'MU' => 'MUR', // Mauritius - Mauritius Rupee
		'MV' => 'MVR', // Maldives - Maldive Rufiyaa
		'MW' => 'MWK', // Malawi - Malawi Kwacha
		'MX' => 'MXN', // Mexico - Mexican Peso
		'MY' => 'MYR', // Malaysia - Malaysian Ringgit
		'MZ' => 'MZN', // Mozambique - Mozambique Metical
		'NA' => 'NAD', // Namibia - Namibian Dollar
		'NC' => 'XPF', // New Caledonia (French) - CFP Franc
		'NE' => 'XOF', // Niger - CFA Franc BCEAO
		'NF' => 'AUD', // Norfolk Island - Australian Dollar
		'NG' => 'NGN', // Nigeria - Nigerian Naira
		'NI' => 'NIO', // Nicaragua - Nicaraguan Cordoba Oro
		'NL' => 'EUR', // Netherlands - Euro
		'NO' => 'NOK', // Norway - Norwegian Krone
		'NP' => 'NPR', // Nepal - Nepalese Rupee
		'NR' => 'AUD', // Nauru - Australian Dollar
		'NU' => 'NZD', // Niue - New Zealand Dollar
		'NZ' => 'NZD', // New Zealand - New Zealand Dollar
		'OM' => 'OMR', // Oman - Omani Rial
		'PA' => 'PAB', // Panama - Panamanian Balboa
		'PE' => 'PEN', // Peru - Peruvian Nuevo Sol
		'PF' => 'XPF', // Polynesia (French) - CFP Franc
		'PG' => 'PGK', // Papua New Guinea - Papua New Guinea Kina
		'PH' => 'PHP', // Philippines - Philippine Peso
		'PK' => 'PKR', // Pakistan - Pakistan Rupee
		'PL' => 'PLN', // Poland - Polish Zloty
		'PM' => 'EUR', // Saint Pierre and Miquelon - Euro
		'PN' => 'NZD', // Pitcairn Island - New Zealand Dollar
		'PR' => 'USD', // Puerto Rico - US Dollar
		'PS' => 'ILS', // Palestinian Territories - Israeli New Shekel
		'PT' => 'EUR', // Portugal - Euro
		'PW' => 'USD', // Palau - US Dollar
		'PY' => 'PYG', // Paraguay - Paraguay Guarani
		'QA' => 'QAR', // Qatar - Qatari Rial
		'RE' => 'EUR', // Reunion (French) - Euro
		'RO' => 'RON', // Romania - Romanian New Leu
		'RS' => 'RSD', // Serbia - Serbian Dinar
		'RU' => 'RUB', // Russia - Russian Ruble
		'RW' => 'RWF', // Rwanda - Rwanda Franc
		'SA' => 'SAR', // Saudi Arabia - Saudi Riyal
		'SB' => 'SBD', // Solomon Islands - Solomon Islands Dollar
		'SC' => 'SCR', // Seychelles - Seychelles Rupee
		'SD' => 'SDG', // Sudan - Sudanese Pound
		'SE' => 'SEK', // Sweden - Swedish Krona
		'SG' => 'SGD', // Singapore - Singapore Dollar
		'SH' => 'SHP', // Saint Helena - St. Helena Pound
		'SI' => 'EUR', // Slovenia - Euro
		'SJ' => 'NOK', // Svalbard and Jan Mayen Islands - Norwegian Krone
		'SK' => 'EUR', // Slovakia - Euro
		'SL' => 'SLL', // Sierra Leone - Sierra Leone Leone
		'SM' => 'EUR', // San Marino - Euro
		'SN' => 'XOF', // Senegal - CFA Franc BCEAO
		'SO' => 'SOS', // Somalia - Somali Shilling
		'SR' => 'SRD', // Suriname - Surinamese Dollar
		'SS' => 'SSP', // South Sudan - South Sudanese Pound
		'ST' => 'STD', // Sao Tome and Principe - Dobra
		'SV' => 'USD', // El Salvador - US Dollar
		'SX' => 'ANG', // Sint Maarten (Dutch Part) - Netherlands Antillean Guilder
		'SY' => 'SYP', // Syria - Syrian Pound
		'SZ' => 'SZL', // Swaziland - Swaziland Lilangeni
		'TC' => 'USD', // Turks and Caicos Islands - US Dollar
		'TD' => 'XAF', // Chad - CFA Franc BEAC
		'TF' => 'EUR', // French Southern Territories - Euro
		'TG' => 'XOF', // Togo - CFA Franc BCEAO
		'TH' => 'THB', // Thailand - Thai Baht
		'TJ' => 'TJS', // Tajikistan - Tajik Somoni
		'TK' => 'NZD', // Tokelau - New Zealand Dollar
		'TL' => 'USD', // Timor-Leste - US Dollar
		'TM' => 'TMM', // Turkmenistan - Manat
		'TN' => 'TND', // Tunisia - Tunisian Dinar
		'TO' => 'TOP', // Tonga - Tongan Pa&#699;anga
		'TR' => 'TRY', // Turkey - Turkish Lira
		'TT' => 'TTD', // Trinidad and Tobago - Trinidad and Tobago Dollar
		'TV' => 'AUD', // Tuvalu - Australian Dollar
		'TW' => 'TWD', // Taiwan - New Taiwan Dollar
		'TZ' => 'TZS', // Tanzania - Tanzanian Shilling
		'UA' => 'UAH', // Ukraine - Ukraine Hryvnia
		'UG' => 'UGX', // Uganda - Uganda Shilling
		'UM' => 'USD', // USA Minor Outlying Islands - US Dollar
		'US' => 'USD', // USA - US Dollar
		'UY' => 'UYU', // Uruguay - Uruguayan Peso
		'UZ' => 'UZS', // Uzbekistan - Uzbekistan Sum
		'VA' => 'EUR', // Vatican - Euro
		'VC' => 'XCD', // Saint Vincent & Grenadines - East Caribbean Dollar
		'VE' => 'VEF', // Venezuela - Venezuelan Bolivar Fuerte
		'VG' => 'USD', // Virgin Islands (British) - US Dollar
		'VI' => 'USD', // Virgin Islands (USA) - US Dollar
		'VN' => 'VND', // Vietnam - Vietnamese Dong
		'VU' => 'VUV', // Vanuatu - Vanuatu Vatu
		'WF' => 'XPF', // Wallis and Futuna Islands - CFP Franc
		'WS' => 'WST', // Samoa - Samoan Tala
		'YE' => 'YER', // Yemen - Yemeni Rial
		'YT' => 'EUR', // Mayotte - Euro
		'ZA' => 'ZAR', // South Africa - South African Rand
		'ZM' => 'ZMK', // Zambia - Zambian Kwacha
		'ZW' => 'USD', // Zimbabwe - US Dollar
	);

	/**
	 * Returns a list containing the currency to be used for each country. The
	 * method implements a filter to allow altering the currency for each country,
	 * if needed.
	 *
	 * @return array
	 */
	protected function country_currencies() {
		return apply_filters('wc_aelia_currencyswitcher_country_currencies', $this->_country_currencies);
	}

	/**
	 * Returns a list containing all world currencies.
	 *
	 * @return array
	 */
	public static function world_currencies() {
		if(empty(self::$_world_currencies)) {
			// Initialise world currencies
			self::$_world_currencies = array(
				'AED' => __('United Arab Emirates dirham', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AFN' => __('Afghan afghani', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ALL' => __('Albanian lek', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AMD' => __('Armenian dram', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ANG' => __('Netherlands Antillean guilder', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AOA' => __('Angolan kwanza', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ARS' => __('Argentine peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AUD' => __('Australian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AWG' => __('Aruban florin', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'AZN' => __('Azerbaijani manat', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BAM' => __('Bosnia and Herzegovina convertible mark', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BBD' => __('Barbadian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BDT' => __('Bangladeshi taka', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BGN' => __('Bulgarian lev', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BHD' => __('Bahraini dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BIF' => __('Burundian franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BMD' => __('Bermudian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BND' => __('Brunei dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BOB' => __('Bolivian boliviano', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BRL' => __('Brazilian real', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BSD' => __('Bahamian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BTN' => __('Bhutanese ngultrum', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BWP' => __('Botswana pula', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BYR' => __('Belarusian ruble', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'BZD' => __('Belize dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CAD' => __('Canadian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CDF' => __('Congolese franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CHF' => __('Swiss franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CLP' => __('Chilean peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CNY' => __('Chinese yuan', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'COP' => __('Colombian peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CRC' => __('Costa Rican colón', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CUC' => __('Cuban convertible peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CUP' => __('Cuban peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CVE' => __('Cape Verdean escudo', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'CZK' => __('Czech koruna', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'DJF' => __('Djiboutian franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'DKK' => __('Danish krone', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'DOP' => __('Dominican peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'DZD' => __('Algerian dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'EGP' => __('Egyptian pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ERN' => __('Eritrean nakfa', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ETB' => __('Ethiopian birr', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'EUR' => __('Euro', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'FJD' => __('Fijian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'FKP' => __('Falkland Islands pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GBP' => __('British pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GEL' => __('Georgian lari', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GGP' => __('Guernsey pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GHS' => __('Ghana cedi', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GIP' => __('Gibraltar pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GMD' => __('Gambian dalasi', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GNF' => __('Guinean franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GTQ' => __('Guatemalan quetzal', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'GYD' => __('Guyanese dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'HKD' => __('Hong Kong dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'HNL' => __('Honduran lempira', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'HRK' => __('Croatian kuna', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'HTG' => __('Haitian gourde', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'HUF' => __('Hungarian forint', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'IDR' => __('Indonesian rupiah', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ILS' => __('Israeli new shekel', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'IMP' => __('Manx pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'INR' => __('Indian rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'IQD' => __('Iraqi dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'IRR' => __('Iranian rial', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ISK' => __('Icelandic króna', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'JEP' => __('Jersey pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'JMD' => __('Jamaican dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'JOD' => __('Jordanian dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'JPY' => __('Japanese yen', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KES' => __('Kenyan shilling', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KGS' => __('Kyrgyzstani som', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KHR' => __('Cambodian riel', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KMF' => __('Comorian franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KPW' => __('North Korean won', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KRW' => __('South Korean won', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KWD' => __('Kuwaiti dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KYD' => __('Cayman Islands dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'KZT' => __('Kazakhstani tenge', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LAK' => __('Lao kip', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LBP' => __('Lebanese pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LKR' => __('Sri Lankan rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LRD' => __('Liberian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LSL' => __('Lesotho loti', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LTL' => __('Lithuanian litas', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'LYD' => __('Libyan dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MAD' => __('Moroccan dirham', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MDL' => __('Moldovan leu', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MGA' => __('Malagasy ariary', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MKD' => __('Macedonian denar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MMK' => __('Burmese kyat', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MNT' => __('Mongolian tögrög', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MOP' => __('Macanese pataca', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MRO' => __('Mauritanian ouguiya', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MUR' => __('Mauritian rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MVR' => __('Maldivian rufiyaa', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MWK' => __('Malawian kwacha', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MXN' => __('Mexican peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MYR' => __('Malaysian ringgit', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'MZN' => __('Mozambican metical', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NAD' => __('Namibian dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NGN' => __('Nigerian naira', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NIO' => __('Nicaraguan córdoba', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NOK' => __('Norwegian krone', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NPR' => __('Nepalese rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'NZD' => __('New Zealand dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'OMR' => __('Omani rial', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PAB' => __('Panamanian balboa', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PEN' => __('Peruvian nuevo sol', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PGK' => __('Papua New Guinean kina', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PHP' => __('Philippine peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PKR' => __('Pakistani rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PLN' => __('Polish złoty', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PRB' => __('Transnistrian ruble', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'PYG' => __('Paraguayan guaraní', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'QAR' => __('Qatari riyal', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'RON' => __('Romanian leu', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'RSD' => __('Serbian dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'RUB' => __('Russian ruble', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'RWF' => __('Rwandan franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SAR' => __('Saudi riyal', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SBD' => __('Solomon Islands dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SCR' => __('Seychellois rupee', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SDG' => __('Sudanese pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SEK' => __('Swedish krona', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SGD' => __('Singapore dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SHP' => __('Saint Helena pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SLL' => __('Sierra Leonean leone', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SOS' => __('Somali shilling', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SRD' => __('Surinamese dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SSP' => __('South Sudanese pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'STD' => __('São Tomé and Príncipe dobra', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SYP' => __('Syrian pound', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'SZL' => __('Swazi lilangeni', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'THB' => __('Thai baht', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TJS' => __('Tajikistani somoni', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TMT' => __('Turkmenistan manat', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TND' => __('Tunisian dinar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TOP' => __('Tongan paʻanga', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TRY' => __('Turkish lira', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TTD' => __('Trinidad and Tobago dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TWD' => __('New Taiwan dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'TZS' => __('Tanzanian shilling', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'UAH' => __('Ukrainian hryvnia', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'UGX' => __('Ugandan shilling', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'USD' => __('United States dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'UYU' => __('Uruguayan peso', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'UZS' => __('Uzbekistani som', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'VEF' => __('Venezuelan bolívar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'VND' => __('Vietnamese đồng', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'VUV' => __('Vanuatu vatu', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'WST' => __('Samoan tālā', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'XAF' => __('Central African CFA franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'XCD' => __('East Caribbean dollar', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'XOF' => __('West African CFA franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'XPF' => __('CFP franc', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'YER' => __('Yemeni rial', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ZAR' => __('South African rand', AELIA_CS_PLUGIN_TEXTDOMAIN),
				'ZMW' => __('Zambian kwacha', AELIA_CS_PLUGIN_TEXTDOMAIN),
			);
		}
		return apply_filters('wc_aelia_currencyswitcher_world_currencies', self::$_world_currencies);
	}

	/**
	 * Returns the Currency used in a specific Country.
	 *
	 * @param string country_code The Country Code.
	 * @return string A Currency Code.
	 */
	public function get_country_currency($country_code) {
		$country_currencies = $this->country_currencies();
		return get_value($country_code, $country_currencies);
	}

	/**
	 * Returns the Currency used in the Country to which a specific IP Address
	 * belongs.
	 *
	 * @param string host A host name or IP Address.
	 * @param string default_currency The Currency to use as a default in case the
	 * Country currency could not be detected.
	 * @return string|bool A currency code, or False if an error occurred.
	 */
	public function get_currency_by_host($host, $default_currency) {
		$ip2location = WC_Aelia_IP2Location::factory();
		$country_code = $ip2location->get_country_code($host);

		Logger::log(sprintf(__('Visitor\'s IP address: %s. Detected country code: %s', AELIA_CS_PLUGIN_TEXTDOMAIN),
												$ip2location->get_visitor_ip_address(),
												$country_code));

		if($country_code === false) {
			Logger::log(sprintf(__('Could not retrieve Country Code for host "%s". Using '.
														 'default currency: %s. Error messages (JSON): %s.',
														 AELIA_CS_PLUGIN_TEXTDOMAIN),
													$host,
													$default_currency,
													json_encode($ip2location->get_errors())));
			return $default_currency;
		}

		$country_currency = $this->get_country_currency($country_code);

		if(WC_Aelia_CurrencySwitcher::settings()->is_currency_enabled($country_currency)) {
			return $country_currency;
		}
		else {
			return $default_currency;
		}
	}

	/**
	 * Given a currency code, it returns the currency's name. If currency is not
	 * found amongst the available ones, its code is returned instead.
	 *
	 * @param string currency The currency code.
	 * @return string
	 */
	public static function get_currency_name($currency) {
		$available_currencies = get_woocommerce_currencies();
		return get_value($currency, $available_currencies, $currency);
	}

	/**
	 * Factory method.
	 *
	 * return WC_Aelia_Currencies_Manager
	 */
	public static function factory() {
		return new self();
	}
}
