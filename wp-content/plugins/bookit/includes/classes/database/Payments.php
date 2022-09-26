<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Payments extends DatabaseModel {

	public static $defaultType    = 'locally';
	public static $freeType       = 'free';
	public static $completeType   = 'complete';
	public static $defaultStatus  = 'pending';
	public static $completeStatus = 'complete';
	public static $rejectedStatus = 'rejected';
	public static $statusList     = [ 'pending', 'cancelled', 'rejected', 'complete' ];
	public static $typeList       = [ 'locally', 'paypal', 'stripe', 'woocommerce', 'free' ];

	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name     = self::_table();
		$primary_key    = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS  {$table_name} (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`appointment_id` INT UNSIGNED NOT NULL,
			`coupon_id` INT UNSIGNED DEFAULT NULL,
			`discount_id` INT UNSIGNED DEFAULT NULL,
			`type` ENUM('locally', 'paypal', 'stripe', 'woocommerce', 'free') NOT NULL DEFAULT 'locally',
			`status` ENUM('pending', 'cancelled', 'rejected', 'complete') NOT NULL DEFAULT 'pending',
			`total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `tax`       DECIMAL(10,2) DEFAULT 0.00,
            `transaction`     VARCHAR(255) DEFAULT NULL,
			`notes` longtext DEFAULT NULL,
			`created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            `paid_at` DATETIME,
    		PRIMARY KEY ({$primary_key}),
            INDEX `idx_appointment_id` (`appointment_id`),
            INDEX `idx_coupon_id` (`coupon_id`),
            INDEX `idx_discount_id` (`discount_id`),
            INDEX `idx_status` (`status`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

	/**
	 * Change Payment Status
	 * @param $id
	 * @param $payment_status
	 */
	public static function change_payment_status( $id, $payment_status ) {
		$data   = array( 'status' => $payment_status );
		$where  = array( 'id' => $id );

		if ($payment_status == 'complete') {
			$data['paid_at'] = wp_date('Y-m-d H:i:s');
		}
		self::update( $data, $where );
	}
}