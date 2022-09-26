<?php

namespace Bookit\Helpers;

use Google\Exception;

/**
 * Bookit Log Helper
 */

define( 'BOOKIT_LOG_PATH', realpath(__DIR__ . '/..' . '/..') . '/logs/' );

class LogHelper {

	public static function writeLogs( $fileName, $args ) {
		$data  = date("Y-m-d H:i:s") . "\n";
		$data .= "DEBUG DATA:\n";
		$data .= json_encode(debug_backtrace(false, 2)) . "\n";

		$data .= "DATA:\n";

		if ($args instanceof Exception) {
			$data .= json_encode($data->getMessage()) . "\n";
		}else {
			$data .= json_encode($data) . "\n";
		}
		$data .= "\n---------------------------\n\n";

		$logFile = BOOKIT_LOG_PATH. $fileName . '.txt';
		if ( ! is_dir( BOOKIT_LOG_PATH ) ) {
			wp_mkdir_p( BOOKIT_LOG_PATH );
			chmod( BOOKIT_LOG_PATH, 0777 );
		}

		file_put_contents($logFile, $data, FILE_APPEND);
	}

}