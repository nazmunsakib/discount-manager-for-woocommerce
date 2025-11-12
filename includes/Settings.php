<?php
/**
 * Settings management
 *
 * @package Dmwoo
 * @author Nazmun Sakib
 * @since 1.0.0
 */

namespace Dmwoo;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
class Settings {

	/**
	 * Get setting value
	 *
	 * @param string $key Setting key.
	 * @param mixed $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_settings';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table; // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( ! $table_exists ) {
			return $default;
		}
		
		$value = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT option_value FROM {$wpdb->prefix}dmwoo_settings WHERE option_name = %s",
			$key
		) );
		
		if ( $value !== null ) {
			return maybe_unserialize( $value );
		}
		
		return $default;
	}

	/**
	 * Set setting value
	 *
	 * @param string $key Setting key.
	 * @param mixed $value Setting value.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_settings';
		
		$serialized_value = maybe_serialize( $value );
		
		$result = $wpdb->replace( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array(
				'option_name' => $key,
				'option_value' => $serialized_value,
			),
			array( '%s', '%s' )
		);
		
		return $result !== false;
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public static function get_all() {
		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_settings';
		
		$results = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->prefix}dmwoo_settings", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		
		$settings = array();
		foreach ( $results as $row ) {
			$settings[ $row['option_name'] ] = maybe_unserialize( $row['option_value'] );
		}
		
		return $settings;
	}

	/**
	 * Get default settings
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'calculate_from' => 'regular_price',
			'apply_product_discount_to' => 'first',
			'coupon_behavior' => 'run_both',
			'suppress_third_party' => false,
			'show_sale_badge' => 'when_condition_matches',
			'show_strikeout' => 1,
			'show_bulk_table' => 1,
		);
	}

	/**
	 * Initialize default settings
	 */
	public static function init_defaults() {
		$defaults = self::get_defaults();
		
		foreach ( $defaults as $key => $value ) {
			if ( self::get( $key ) === null ) {
				self::set( $key, $value );
			}
		}
	}
}