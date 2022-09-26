<?php
/**
 * Bookit Helpers
 */

/**
 * Generate Price
 * @param $price
 * @return string
 */
function bookit_price( $price ) {
	$settings           = \Bookit\Classes\Admin\SettingsController::get_settings();
	$formatted_price    = number_format($price, $settings['decimals_number'], $settings['decimals_separator'], $settings['thousands_separator']);

	if ( $settings['currency_position'] == 'left' ) {
		$formatted_price = $settings['currency_symbol'] . $formatted_price;
	} else {
		$formatted_price .= $settings['currency_symbol'];
	}

	return $formatted_price;
}

/**
 * Translated Date Time
 * @param $format
 * @param $timestamp
 * @return mixed
 */
function bookit_datetime_i18n( $format, $timestamp ) {
	$timezone_str   = get_option('timezone_string') ?: 'UTC';
	$timezone       = new \DateTimeZone($timezone_str);

	// The date in the local timezone.
	$date           = new \DateTime(null, $timezone);
	$date->setTimestamp($timestamp);
	$date_string    = $date->format('Y-m-d H:i:s');

	// Pretend the local date is UTC to get the timestamp
	// to pass to date_i18n().
	$utc_timezone   = new \DateTimeZone('UTC');
	$utc_date       = new \DateTime($date_string, $utc_timezone);
	$timestamp      = $utc_date->getTimestamp();

	return date_i18n($format, $timestamp, true);
}

/**
 * Generate List
 * @param $data
 * @param $key
 * @param $value
 * @param $elementor
 * @return array
 */
function bookit_data_to_list( $data, $key, $value, $elementor = false ) {
	if ( $elementor ) {
		$list = [ '' => esc_html__('Keep empty', 'bookit') ];
	} else {
		$list = [ esc_html__('Keep empty', 'bookit') => '' ];
	}

	if ( count($data) > 0 ) {
		foreach ( $data as $item ) {
			$list[$item[$key]] = $item[$value];
		}
	} else {
		if ( $elementor ) {
			$list = [ '' => esc_html__('Nothing found', 'bookit') ];
		} else {
			$list = [ esc_html__('Nothing found', 'bookit') => '' ];
		}
	}

	return $list;
}
/**
 * Convert PHP To Moment JS Date Format
 * @param $format
 * @return string
 */
function bookit_php_to_moment( $format ) {
	$replacements = [
		'd' => 'DD',
		'D' => 'ddd',
		'j' => 'D',
		'l' => 'dddd',
		'N' => 'E',
		'S' => 'o',
		'w' => 'e',
		'z' => 'DDD',
		'W' => 'W',
		'F' => 'MMMM',
		'm' => 'MM',
		'M' => 'MMM',
		'n' => 'M',
		't' => '', // no equivalent
		'L' => '', // no equivalent
		'o' => 'YYYY',
		'Y' => 'YYYY',
		'y' => 'YY',
		'a' => 'a',
		'A' => 'A',
		'B' => '', // no equivalent
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'SSS',
		'e' => 'zz', // deprecated since version 1.6.0 of moment.js
		'I' => '', // no equivalent
		'O' => '', // no equivalent
		'P' => '', // no equivalent
		'T' => '', // no equivalent
		'Z' => '', // no equivalent
		'c' => '', // no equivalent
		'r' => '', // no equivalent
		'U' => 'X',
	];
	$momentFormat = strtr($format, $replacements);
	return $momentFormat;
}

/**
 * Check if Pro plugin is active
 * @return bool
 */
function bookit_pro_active() {//todo remove
	return defined("BOOKIT_PRO_VERSION");
}

/**
 * Disable Pro Features
 * @return bool
 */
function bookit_pro_features_disabled() {//todo remove
	return bookit_pro_active() ? 'false' : 'true';
}

/**
 * Function to get inside array option value
 * key separated by .
 * ex-le: 'bookit_settings.first_key_of_option_bookit_settings.second_key_of_option_bookit_settings
 */

function get_option_by_path( $path ) {

	$keys = explode( '.', $path );
	$option = get_option( $keys[0] );

	if ( ! $option || ! is_array( $option ) ) {
		return false;
	}
	array_shift($keys);

	foreach ( $keys as $key ) {
		if ( is_array( $option )  && array_key_exists( $key, $option ) ) {
			$option = $option[ $key ];
		}else{
			return false;
		}
	}
	return $option;
}

/**
 * @param string $path | dot-delimited nested key values  'data.settings.etc'
 * @param array $array
 *
 * @return false|mixed
 */
function get_deep_array_value_by_path( $path, $array ) {
	$keys = explode( '.', $path );

	foreach ( $keys as $key ) {
		if ( is_array( $array )  && array_key_exists( $key, $array ) ) {
			$array = $array[ $key ];
		}else{
			return false;
		}
	}

	return $array;
}

function bookit_crypt( $value ){
	$vector = "1234567890123412";
	$data   = openssl_encrypt($value, 'aes-256-cbc', BOOKIT_FILE, OPENSSL_RAW_DATA, $vector);
	return base64_encode($data);
}

function bookit_decrypt( $value ){
	$vector ="1234567890123412";
	$value  = base64_decode($value);
	$data   = openssl_decrypt($value, 'aes-256-cbc', BOOKIT_FILE, OPENSSL_RAW_DATA, $vector);
	return $data;
}

/** check is date string by format */
function isDateByFormat($date, $format = 'Y-m-d H:i:s') {
	$dateVar = DateTime::createFromFormat($format, $date);
	return $dateVar && $dateVar->format($format) == $date;
}