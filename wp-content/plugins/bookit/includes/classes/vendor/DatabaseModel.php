<?php

namespace Bookit\Classes\Vendor;

abstract class DatabaseModel {

	public static $primary_key = 'id';
	protected static $table_prefix = 'bookit_';

	/**
	 * Generate Table Name from Called Class
	 * @return string
	 */
	public static function _table() {
		global $wpdb;
		$classname = explode( '\\', strtolower( get_called_class() ) );
		$tablename = self::$table_prefix . end( $classname );
		return $wpdb->prefix . $tablename;
	}

	/**
	 * SQL Fetch from Table
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	private static function _fetch_sql( $key, $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), $key );
		return $wpdb->prepare( $sql, $value );
	}

	/**
	 * Get Rows with Pagination
	 *
	 * @param $limit
	 * @param $offset
	 * @param string $search
	 * @param string $sort
	 * @param string $order
	 *
	 * @return mixed
	 */
	public static function get_paged($limit, $offset, $search = '', $sort = '', $order = '') {
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM %s %s ORDER BY %s %s LIMIT %%d OFFSET %%d',
			self::_table(),
			$search,
			( empty( $sort ) ) ? static::$primary_key : $sort,
			( empty( $order ) ) ? 'DESC' : $order
		);
		return $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				intval($limit),
				intval($offset)
			),
			ARRAY_A
		);
	}

	/**
	 * Get All Rows
	 */
	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results(
			sprintf( 'SELECT * FROM %s ORDER BY %s DESC', self::_table(), static::$primary_key ),
			ARRAY_A
		);
	}

	/**
	 * Get Total Count of Rows
	 * @return mixed
	 */
	public static function get_count() {
		global $wpdb;
		return $wpdb->get_var( sprintf( 'SELECT COUNT(*) FROM %s', self::_table() ) );
	}

	/**
	 * Get Row by ID
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public static function get( $key, $value ) {
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $key, $value ) );
	}

	/**
	 * Insert data to Table
	 * @param $data
	 */
	public static function insert( $data ) {
		global $wpdb;

		add_filter( 'query', [self::class, 'wp_db_null_value'] );

		if ( isset($data['nonce']) ) unset( $data['nonce'] );

		$data = array_map(function($item) {
			if(trim($item, ' \'"')){
				return trim($item);
			}
			return null;
		}, $data);

		$wpdb->insert( self::_table(), $data );

		remove_filter( 'query', [self::class, 'wp_db_null_value'] );
	}

	/**
	 * Update data in Table with $where clause
	 * @param $data
	 * @param $where
	 */
	public static function update( $data, $where ) {
		global $wpdb;

		add_filter( 'query', [self::class, 'wp_db_null_value'] );

		if ( isset($data['nonce']) ) unset( $data['nonce'] );

		$data = array_map(function($item) {
			if(trim($item, ' \'"')){
				return trim($item);
			}
			return null;
		}, $data);

		$wpdb->update( self::_table(), $data, $where );

		remove_filter( 'query', [self::class, 'wp_db_null_value'] );
	}

	/**
	 * Delete data from Table by ID
	 * @param $value
	 * @return mixed
	 */
	public static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Delete data from Table Where
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public static function delete_where( $key, $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), $key );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Get Inserted data ID
	 * @return mixed
	 */
	public static function insert_id() {
		global $wpdb;
		return $wpdb->insert_id;
	}

	/**
	 * Replace the 'NULL' string with NULL
	 *
	 * @param  string $query
	 * @return string $query
	 */
	public static function wp_db_null_value( $query )
	{
		return str_ireplace( "'NULL'", "NULL", $query );
	}

	/**
	 * Show Last Query
	 * @return mixed
	 */
	public static function show_last_query() {
		global $wpdb;
		echo $wpdb->last_query;
	}

	/**
	 * Drop all bookit tables on uninstall
	 */
	public static function drop_tables() {
		global $wpdb;

		$sql = self::generate_drop_table_statement();
		$wpdb->query( $sql );
	}

	/**
	 * Generate sql statement to remove all bookit tables by prefix
	 */
	private static function generate_drop_table_statement() {
		global $wpdb;

		$prefix = $wpdb->prefix . self::$table_prefix;
		return $wpdb->get_var(
			"SELECT CONCAT( 'DROP TABLE ', GROUP_CONCAT(DISTINCT( table_name) ) , ';' )  AS statement
			FROM information_schema.tables 
			WHERE table_name LIKE '%{$prefix}%'"
		);
	}
}