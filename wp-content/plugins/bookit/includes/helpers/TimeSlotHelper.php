<?php

namespace Bookit\Helpers;

use Bookit\Classes\Admin\SettingsController;

/**
 * Bookit Time Slot Helper
 */


class TimeSlotHelper {

	/** slot length **/
	const TIME_SLOT_POSSIBLE_VALUES = [
		'15_minutes' => 15,
		'20_minutes' => 20,
		'30_minutes' => 30,
		'45_minutes' => 45,
		'1_hour' => 60
	];
	const DAY_IN_SECONDS = 60 * 60 * 24;

	/**
	 * @param integer $start - time in seconds
	 * @param integer $end - time in seconds
	 * @param string $type - possible values "default|admin"
	 */
	public static function getTimeList($start, $end) {
		$result             = [];
		$time_slot_duration = get_option_by_path('bookit_settings.time_slot_duration') ?: SettingsController::$time_slot_default_duration;

		while ( $start <= $end ) {
			$slot = [
				'value' => self::timeFromSeconds($start),
				'label' => self::timeFormat( $start ),
			];
			$result[] = $slot;
			$start += ( (int)$time_slot_duration * 60 );
		}

		return $result;
	}

	public static function timeFormat($time) {
		return date_i18n( get_option( 'time_format' ), is_numeric( $time ) ? $time : strtotime( $time, current_time( 'timestamp' ) ) );
	}

	/**
	 * Build time string from seconds
	 *
	 * @param int $seconds
	 * @return string
	 */
	public static function timeFromSeconds( $seconds ) {
		$seconds  = abs( $seconds );
		$hours    = (int) ( $seconds / 3600 );
		$seconds -= $hours * 3600;
		$minutes  = (int) ( $seconds / 60 );
		$seconds -= $minutes * 60;

		return sprintf( '%s%02d:%02d:%02d', $seconds < 0 ? '-' : '', $hours, $minutes, $seconds );
	}

}