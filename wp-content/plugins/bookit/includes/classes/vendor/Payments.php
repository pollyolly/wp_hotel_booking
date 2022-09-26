<?php

namespace Bookit\Classes\Vendor;

use Bookit\Classes\Database\Services;
use Bookit\Helpers\AddonHelper;

class Payments {

	private $redirect_url;
	private $appointment;
	private $payment_method;
	private $service;
	private $className;
	private $token;

	public function __construct( $appointment = [] ) {
		$this->appointment      = $appointment;
		$this->payment_method   = $appointment['payment_method'];
		$this->token            = $appointment['token'];
		$this->service          = Services::get('id', $this->appointment['service_id']);
		$this->className        = sprintf('%s\Classes\Payments\%s', $this->getPaymentPluginName(),ucwords($this->payment_method));

		$this->{$this->payment_method}();
	}

	/** Used while Bookit Pro is alive */
	private function getPaymentPluginName() {
		$isProInstalled = AddonHelper::checkIsInstalledPlugin('bookit-pro/bookit-pro.php');
		$isProActive = bookit_pro_active();

		$isPaymentsInstalled = AddonHelper::checkIsInstalledPlugin('bookit-payments/bookit-payments.php');
		$isPaymentsActive = defined("BOOKIT_PAYMENTS_VERSION");;

		if ( $isPaymentsInstalled && $isPaymentsActive ) {
			return 'BookitPayments';
		}

		if ( $isProInstalled && $isProActive ) {
			return 'BookitPro';
		}
	}

	/**
	 * Free
	 */
	public function free() {
		$this->redirect_url = '';
	}

	/**
	 * Pay Locally
	 */
	public function locally() {
		$this->redirect_url = '';
	}

	/**
	 * PayPal
	 */
	public function paypal() {
		$className  = $this->className;

		$paypal     = new $className(
			$this->appointment['price'],
			$this->appointment['id'],
			$this->service->title,
			$this->service->id,
			$this->appointment['customer_email'],
			''
		);

		$this->redirect_url = $paypal->generate_payment_url();
	}

	/**
	 * Stripe
	 */
	public function stripe() {
		$className  = $this->className;

		$stripe     = new $className(
			$this->token,
			$this->appointment['price'],
			$this->appointment['id']
		);
		$stripe->check_payment();

		$this->redirect_url = '';
	}

	/**
	 * WooCommerce
	 */
	public function woocommerce() {
		$className  = $this->className;

		$paypal     = new $className(
			$this->appointment['price'],
			$this->appointment['id'],
			$this->service->title
		);

		$this->redirect_url = $paypal->generate_payment_url();
	}

	/**
	 * @return string
	 */
	public function redirect_url() {
		return $this->redirect_url;
	}

	/**
	 * Currency list used for payments
	 * @return array
	 */
	public static function get_currency_list() {
		return [
			[ 'alias' => esc_html__('United Arab Emirates dirham'), 'value' => 'AED', 'is_zero_decimal' => false, 'symbol' => '&#x62f;.&#x625;' ],
			[ 'alias' => esc_html__('Afghan afghani'), 'value' => 'AFN', 'is_zero_decimal' => false, 'symbol' => '&#x60b;' ],
			[ 'alias' => esc_html__('Albanian lek'), 'value' => 'ALL', 'is_zero_decimal' => false, 'symbol' => 'L' ],
			[ 'alias' => esc_html__('Armenian dram'), 'value' => 'AMD', 'is_zero_decimal' => false, 'symbol' => 'AMD' ],
			[ 'alias' => esc_html__('Netherlands Antillean guilder'), 'value' => 'ANG', 'is_zero_decimal' => false, 'symbol' => '&fnof;' ],
			[ 'alias' => esc_html__('Angolan kwanza'), 'value' => 'AOA', 'is_zero_decimal' => false, 'symbol' => 'Kz' ],
			[ 'alias' => esc_html__('Argentine peso'), 'value' => 'ARS', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Australian dollar'), 'value' => esc_html__('AUD'), 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Aruban florin'), 'value' => 'AWG', 'is_zero_decimal' => false, 'symbol' => 'Afl.' ],
			[ 'alias' => esc_html__('Azerbaijani manat'), 'value' => 'AZN', 'is_zero_decimal' => false, 'symbol' => 'AZN' ],
			[ 'alias' => esc_html__('Bosnia and Herzegovina convertible mark'), 'value' => 'BAM', 'is_zero_decimal' => false, 'symbol' => 'KM' ],
			[ 'alias' => esc_html__('Barbadian dollar'), 'value' => 'BBD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Bangladeshi taka'), 'value' => 'BDT', 'is_zero_decimal' => false, 'symbol' => '&#2547;&nbsp;' ],
			[ 'alias' => esc_html__('Bulgarian lev'), 'value' => 'BGN', 'is_zero_decimal' => false, 'symbol' => '&#1083;&#1074;.' ],
			[ 'alias' => esc_html__('Bahraini dinar'), 'value' => 'BHD', 'is_zero_decimal' => false, 'symbol' => '.&#x62f;.&#x628;' ],
			[ 'alias' => esc_html__('Burundian franc'), 'value' => 'BIF', 'is_zero_decimal' => true, 'symbol' => 'Fr'],
			[ 'alias' => esc_html__('Bermudian dollar'), 'value' => 'BMD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Brunei dollar'), 'value' => 'BND', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Bolivian boliviano'), 'value' => 'BOB', 'is_zero_decimal' => false, 'symbol' => 'Bs.' ],
			[ 'alias' => esc_html__('Brazilian real'), 'value' => esc_html__('BRL'), 'is_zero_decimal' => false, 'symbol' => '&#82;&#36;' ],
			[ 'alias' => esc_html__('Bahamian dollar'), 'value' => 'BSD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Botswana pula'), 'value' => 'BWP', 'is_zero_decimal' => false, 'symbol' => 'P' ],
			[ 'alias' => esc_html__('Belarusian ruble'), 'value' => 'BYN', 'is_zero_decimal' => false, 'symbol' => 'Br' ],
			[ 'alias' => esc_html__('Belize dollar'), 'value' => 'BZD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Canadian dollar'), 'value' => esc_html__('CAD'), 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Congolese franc'), 'value' => 'CDF', 'is_zero_decimal' => false, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('Swiss franc'), 'value' => esc_html__('CHF'), 'is_zero_decimal' => false, 'symbol' => '&#67;&#72;&#70;' ],
			[ 'alias' => esc_html__('Chilean peso'), 'value' => 'CLP', 'is_zero_decimal' => true, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Chinese yuan'), 'value' => 'CNY', 'is_zero_decimal' => false, 'symbol' => '&yen;' ],
			[ 'alias' => esc_html__('Colombian peso'), 'value' => 'COP', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Costa Rican col&oacute;n'), 'value' => 'CRC', 'is_zero_decimal' => false, 'symbol' => '&#x20a1;' ],
			[ 'alias' => esc_html__('Cuban convertible peso'), 'value' => 'CUC', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Cuban peso'), 'value' => 'CUP', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Cape Verdean escudo'), 'value' => 'CVE', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Czech koruna'), 'value' => esc_html__('CZK'), 'is_zero_decimal' => false, 'symbol' => '&#75;&#269;' ],
			[ 'alias' => esc_html__('Djiboutian franc'), 'value' => 'DJF', 'is_zero_decimal' => true, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('Danish krone'), 'value' => esc_html__('DKK'), 'is_zero_decimal' => false, 'symbol' => 'DKK' ],
			[ 'alias' => esc_html__('Dominican peso'), 'value' => 'DOP', 'is_zero_decimal' => false, 'symbol' => 'RD&#36;' ],
			[ 'alias' => esc_html__('Algerian dinar'), 'value' => 'DZD', 'is_zero_decimal' => false, 'symbol' => '&#x62f;.&#x62c;' ],
			[ 'alias' => esc_html__('Egyptian pound'), 'value' => 'EGP', 'is_zero_decimal' => false, 'symbol' => 'EGP' ],
			[ 'alias' => esc_html__('Eritrean nakfa'), 'value' => 'ERN', 'is_zero_decimal' => false, 'symbol' => 'Nfk' ],
			[ 'alias' => esc_html__('Ethiopian birr'), 'value' => 'ETB', 'is_zero_decimal' => false, 'symbol' => 'Br' ],
			[ 'alias' => esc_html__('Euro'), 'value' => 'EUR', 'is_zero_decimal' => false, 'symbol' => '&euro;' ],
			[ 'alias' => esc_html__('Fijian dollar'), 'value' => 'FJD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Falkland Islands pound'), 'value' => 'FKP', 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Pound sterling'), 'value' => esc_html__('GBP'), 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Georgian lari'), 'value' => 'GEL', 'is_zero_decimal' => false, 'symbol' => '&#x20be;' ],
			[ 'alias' => esc_html__('Guernsey pound'), 'value' => 'GGP', 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Ghana cedi'), 'value' => 'GHS', 'is_zero_decimal' => false, 'symbol' => '&#x20b5;' ],
			[ 'alias' => esc_html__('Gibraltar pound'), 'value' => 'GIP', 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Gambian dalasi'), 'value' => 'GMD', 'is_zero_decimal' => false, 'symbol' => 'D' ],
			[ 'alias' => esc_html__('Guinean franc'), 'value' => 'GNF', 'is_zero_decimal' => true, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('Guatemalan quetzal'), 'value' => 'GTQ', 'is_zero_decimal' => false, 'symbol' => 'Q' ],
			[ 'alias' => esc_html__('Guyanese dollar'), 'value' => 'GYD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Hong Kong dollar'), 'value' => esc_html__('HKD'), 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Honduran lempira'), 'value' => 'HNL', 'is_zero_decimal' => false, 'symbol' => 'L' ],
			[ 'alias' => esc_html__('Croatian kuna'), 'value' => 'HRK', 'is_zero_decimal' => false, 'symbol' => 'kn' ],
			[ 'alias' => esc_html__('Haitian gourde'), 'value' => 'HTG', 'is_zero_decimal' => false, 'symbol' => 'G' ],
			[ 'alias' => esc_html__('Hungarian forint 1'), 'value' => esc_html__('HUF'), 'is_zero_decimal' => false, 'symbol' => '&#70;&#116;' ],
			[ 'alias' => esc_html__('Indonesian rupiah'), 'value' => 'IDR', 'is_zero_decimal' => false, 'symbol' => 'Rp' ],
			[ 'alias' => esc_html__('Israeli new shekel'), 'value' => esc_html__('ILS'), 'is_zero_decimal' => false, 'symbol' => '&#8362;' ],
			[ 'alias' => esc_html__('Indian rupee'), 'value' => esc_html__('INR'), 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Iraqi dinar'), 'value' => 'IQD', 'is_zero_decimal' => false, 'symbol' => '&#x639;.&#x62f;' ],
			[ 'alias' => esc_html__('Iranian rial'), 'value' => 'IRR', 'is_zero_decimal' => false, 'symbol' => '&#xfdfc;' ],
			[ 'alias' => esc_html__('Icelandic kr&oacute;na'), 'value' => 'ISK', 'is_zero_decimal' => false, 'symbol' => 'kr.' ],
			[ 'alias' => esc_html__('Jamaican dollar'), 'value' => 'JMD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Jordanian dinar'), 'value' => 'JOD', 'is_zero_decimal' => false, 'symbol' => '&#x62f;.&#x627;' ],
			[ 'alias' => esc_html__('Japanese yen 1'), 'value' => esc_html__('JPY'), 'is_zero_decimal' => true, 'symbol' => '&yen;' ],
			[ 'alias' => esc_html__('Kenyan shilling'), 'value' => 'KES', 'is_zero_decimal' => false, 'symbol' => 'KSh' ],
			[ 'alias' => esc_html__('Kyrgyzstani som'), 'value' => 'KGS', 'is_zero_decimal' => false, 'symbol' => '&#x441;&#x43e;&#x43c;' ],
			[ 'alias' => esc_html__('Cambodian riel'), 'value' => 'KHR', 'is_zero_decimal' => false, 'symbol' => '&#x17db;' ],
			[ 'alias' => esc_html__('Comorian franc'), 'value' => 'KMF', 'is_zero_decimal' => true, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('North Korean won'), 'value' => 'KPW', 'is_zero_decimal' => false, 'symbol' => '&#x20a9;' ],
			[ 'alias' => esc_html__('South Korean won'), 'value' => 'KRW', 'is_zero_decimal' => true, 'symbol' => '&#8361;' ],
			[ 'alias' => esc_html__('Kuwaiti dinar'), 'value' => 'KWD', 'is_zero_decimal' => false, 'symbol' => '&#x62f;.&#x643;' ],
			[ 'alias' => esc_html__('Cayman Islands dollar'), 'value' => 'KYD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Kazakhstani tenge'), 'value' => 'KZT', 'is_zero_decimal' => false, 'symbol' => '&#8376;' ],
			[ 'alias' => esc_html__('Lao kip'), 'value' => 'LAK', 'is_zero_decimal' => false, 'symbol' => '&#8365;' ],
			[ 'alias' => esc_html__('Lebanese pound'), 'value' => 'LBP', 'is_zero_decimal' => false, 'symbol' => '&#x644;.&#x644;' ],
			[ 'alias' => esc_html__('Sri Lankan rupee'), 'value' => 'LKR', 'is_zero_decimal' => false, 'symbol' => '&#xdbb;&#xdd4;' ],
			[ 'alias' => esc_html__('Liberian dollar'), 'value' => 'LRD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Lesotho loti'), 'value' => 'LSL', 'is_zero_decimal' => false, 'symbol' => 'L' ],
			[ 'alias' => esc_html__('Libyan dinar'), 'value' => 'LYD', 'is_zero_decimal' => false, 'symbol' => '&#x644;.&#x62f;' ],
			[ 'alias' => esc_html__('Moroccan dirham'), 'value' => 'MAD', 'is_zero_decimal' => false, 'symbol' => '&#x62f;.&#x645;.' ],
			[ 'alias' => esc_html__('Moldovan leu'), 'value' => 'MDL', 'is_zero_decimal' => false, 'symbol' => 'MDL' ],
			[ 'alias' => esc_html__('Malagasy ariary'), 'value' => 'MGA', 'is_zero_decimal' => true, 'symbol' => 'Ar' ],
			[ 'alias' => esc_html__('Macedonian denar'), 'value' => 'MKD', 'is_zero_decimal' => false, 'symbol' => '&#x434;&#x435;&#x43d;' ],
			[ 'alias' => esc_html__('Burmese kyat'), 'value' => 'MMK', 'is_zero_decimal' => false, 'symbol' => 'Ks' ],
			[ 'alias' => esc_html__('Mongolian t&ouml;gr&ouml;g'), 'value' => 'MNT', 'is_zero_decimal' => false, 'symbol' => '&#x20ae;' ],
			[ 'alias' => esc_html__('Macanese pataca'), 'value' => 'MOP', 'is_zero_decimal' => false, 'symbol' => 'P' ],
			[ 'alias' => esc_html__('Mauritian rupee'), 'value' => 'MUR', 'is_zero_decimal' => false, 'symbol' => '&#x20a8;' ],
			[ 'alias' => esc_html__('Maldivian rufiyaa'), 'value' => 'MVR', 'is_zero_decimal' => false, 'symbol' => '.&#x783;' ],
			[ 'alias' => esc_html__('Malawian kwacha'), 'value' => 'MWK', 'is_zero_decimal' => false, 'symbol' => 'MK' ],
			[ 'alias' => esc_html__('Mexican peso'), 'value' => esc_html__('MXN'), 'is_zero_decimal' => false, 'symbol' => '&#36;'],
			[ 'alias' => esc_html__('Malaysian ringgit 2'), 'value' => esc_html__('MYR'), 'is_zero_decimal' => false, 'symbol' => '&#82;&#77;' ],
			[ 'alias' => esc_html__('Mozambican metical'), 'value' => 'MZN', 'is_zero_decimal' => false, 'symbol' => 'MT' ],
			[ 'alias' => esc_html__('Namibian dollar'), 'value' => 'NAD', 'is_zero_decimal' => false, 'symbol' => 'N&#36;' ],
			[ 'alias' => esc_html__('Nigerian naira'), 'value' => 'NGN', 'is_zero_decimal' => false, 'symbol' => '&#8358;' ],
			[ 'alias' => esc_html__('Nicaraguan c&oacute;rdoba'), 'value' => 'NIO', 'is_zero_decimal' => false, 'symbol' => 'C&#36;' ],
			[ 'alias' => esc_html__('Norwegian krone'), 'value' => esc_html__('NOK'), 'is_zero_decimal' => false, 'symbol' => '&#107;&#114;' ],
			[ 'alias' => esc_html__('Nepalese rupee'), 'value' => 'NPR', 'is_zero_decimal' => false, 'symbol' => '&#8360;' ],
			[ 'alias' => esc_html__('New Zealand dollar'), 'value' => esc_html__('NZD'), 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Omani rial'), 'value' => 'OMR', 'is_zero_decimal' => false, 'symbol' => '&#x631;.&#x639;.' ],
			[ 'alias' => esc_html__('Panamanian balboa'), 'value' => 'PAB', 'is_zero_decimal' => false, 'symbol' => 'B/.' ],
			[ 'alias' => esc_html__('Sol'), 'value' => 'PEN', 'is_zero_decimal' => false, 'symbol' => 'S/' ],
			[ 'alias' => esc_html__('Papua New Guinean kina'), 'value' => 'PGK', 'is_zero_decimal' => false, 'symbol' => 'K' ],
			[ 'alias' => esc_html__('Philippine peso'), 'value' => esc_html__('PHP'), 'is_zero_decimal' => false, 'symbol' => '&#8369;' ],
			[ 'alias' => esc_html__('Pakistani rupee'), 'value' => 'PKR', 'is_zero_decimal' => false, 'symbol' => '&#8360;' ],
			[ 'alias' => esc_html__('Polish złoty'), 'value' => esc_html__('PLN'), 'is_zero_decimal' => false, 'symbol' => '&#122;&#322;' ],
			[ 'alias' => esc_html__('Paraguayan guaran&iacute;'), 'value' => 'PYG', 'is_zero_decimal' => true, 'symbol' => '&#8370;' ],
			[ 'alias' => esc_html__('Qatari riyal'), 'value' => 'QAR', 'is_zero_decimal' => false, 'symbol' => '&#x631;.&#x642;' ],
			[ 'alias' => esc_html__('Romanian leu'), 'value' => 'RON', 'is_zero_decimal' => false, 'symbol' => 'lei' ],
			[ 'alias' => esc_html__('Serbian dinar'), 'value' => 'RSD', 'is_zero_decimal' => false, 'symbol' => '&#1088;&#1089;&#1076;' ],
			[ 'alias' => esc_html__('Russian ruble'), 'value' => esc_html__('RUB'), 'is_zero_decimal' => false, 'symbol' => '&#8381;' ],
			[ 'alias' => esc_html__('Rwandan franc'), 'value' => 'RWF', 'is_zero_decimal' => true, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('Saudi riyal'), 'value' => 'SAR', 'is_zero_decimal' => false, 'symbol' => '&#x631;.&#x633;' ],
			[ 'alias' => esc_html__('Solomon Islands dollar'), 'value' => 'SBD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Seychellois rupee'), 'value' => 'SCR', 'is_zero_decimal' => false, 'symbol' => '&#x20a8;' ],
			[ 'alias' => esc_html__('Swedish krona'), 'value' => esc_html__('SEK'), 'is_zero_decimal' => false, 'symbol' => '&#107;&#114;' ],
			[ 'alias' => esc_html__('Singapore dollar'), 'value' => esc_html__('SGD'), 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Saint Helena pound'), 'value' => 'SHP', 'is_zero_decimal' => false, 'symbol' => '&pound;' ],
			[ 'alias' => esc_html__('Sierra Leonean leone'), 'value' => 'SLL', 'is_zero_decimal' => false, 'symbol' => 'Le' ],
			[ 'alias' => esc_html__('Somali shilling'), 'value' => 'SOS', 'is_zero_decimal' => false, 'symbol' => 'Sh' ],
			[ 'alias' => esc_html__('Surinamese dollar'), 'value' => 'SRD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Syrian pound'), 'value' => 'SYP', 'is_zero_decimal' => false, 'symbol' => '&#x644;.&#x633;' ],
			[ 'alias' => esc_html__('Swazi lilangeni'), 'value' => 'SZL', 'is_zero_decimal' => false, 'symbol' => 'L' ],
			[ 'alias' => esc_html__('Thai baht'), 'value' => esc_html__('THB'), 'is_zero_decimal' => false, 'symbol' => '&#3647;' ],
			[ 'alias' => esc_html__('Tajikistani somoni'), 'value' => 'TJS', 'is_zero_decimal' => false, 'symbol' => '&#x405;&#x41c;' ],
			[ 'alias' => esc_html__('Turkmenistan manat'), 'value' => 'TMT', 'is_zero_decimal' => false, 'symbol' => 'm' ],
			[ 'alias' => esc_html__('Tongan pa&#x2bb;anga'), 'value' => 'TOP', 'is_zero_decimal' => false, 'symbol' => 'T&#36;' ],
			[ 'alias' => esc_html__('Turkish lira'), 'value' => 'TRY', 'is_zero_decimal' => false, 'symbol' => '&#8378;' ],
			[ 'alias' => esc_html__('Trinidad and Tobago dollar'), 'value' => 'TTD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('New Taiwan dollar 1'), 'value' => esc_html__('TWD'), 'is_zero_decimal' => false, 'symbol' => '&#78;&#84;&#36;' ],
			[ 'alias' => esc_html__('Tanzanian shilling'), 'value' => 'TZS', 'is_zero_decimal' => false, 'symbol' => 'Sh' ],
			[ 'alias' => esc_html__('Ukrainian hryvnia'), 'value' => 'UAH', 'is_zero_decimal' => false, 'symbol' => '&#8372;' ],
			[ 'alias' => esc_html__('Ugandan shilling'), 'value' => 'UGX', 'is_zero_decimal' => true, 'symbol' => 'UGX' ],
			[ 'alias' => esc_html__('United States dollar'), 'value' => esc_html__('USD'),'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Uruguayan peso'), 'value' => 'UYU', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('Uzbekistani som'), 'value' => 'UZS', 'is_zero_decimal' => false, 'symbol' => 'UZS' ],
			[ 'alias' => esc_html__('Vietnamese &#x111;&#x1ed3;ng'), 'value' => 'VND', 'is_zero_decimal' => true, 'symbol' => '&#8363;' ],
			[ 'alias' => esc_html__('Vanuatu vatu'), 'value' => 'VUV', 'is_zero_decimal' => true, 'symbol' => 'Vt' ],
			[ 'alias' => esc_html__('Samoan t&#x101;l&#x101;'), 'value' => 'WST', 'is_zero_decimal' => false, 'symbol' => 'T' ],
			[ 'alias' => esc_html__('Central African CFA franc'), 'value' => 'XAF', 'is_zero_decimal' => true, 'symbol' => 'CFA' ],
			[ 'alias' => esc_html__('East Caribbean dollar'), 'value' => 'XCD', 'is_zero_decimal' => false, 'symbol' => '&#36;' ],
			[ 'alias' => esc_html__('West African CFA franc'), 'value' => 'XOF', 'is_zero_decimal' => true, 'symbol' => 'CFA' ],
			[ 'alias' => esc_html__('CFP franc'), 'value' => 'XPF', 'is_zero_decimal' => true, 'symbol' => 'Fr' ],
			[ 'alias' => esc_html__('Yemeni rial'), 'value' => 'YER', 'is_zero_decimal' => false, 'symbol' => '&#xfdfc;' ],
			[ 'alias' => esc_html__('South African rand'), 'value' => 'ZAR', 'is_zero_decimal' => false, 'symbol' => '&#82;' ],
			[ 'alias' => esc_html__('Zambian kwacha'), 'value' => 'ZMW', 'is_zero_decimal' => false, 'symbol' => 'ZK' ],
		];
	}

}