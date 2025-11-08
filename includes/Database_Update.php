<?php
/**
 * Database update operations
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
 * Database_Update class
 */
class Database_Update {

	/**
	 * Update database schema
	 */
	public static function update_schema() {
		global $wpdb;
		
		$table = $wpdb->prefix . 'dmwoo_rules';
		
		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
			Database::create_tables();
			return;
		}
		
		// Add missing columns
		$columns_to_add = array(
			'exclusive' => 'tinyint(1) DEFAULT 0',
			'bulk_ranges' => 'longtext',
			'cart_label' => 'varchar(255) DEFAULT NULL',
			'apply_as_cart_rule' => 'tinyint(1) DEFAULT 0',
			'bulk_operator' => 'varchar(50) DEFAULT "product_cumulative"',
			'badge_settings' => 'longtext',
			'free_shipping' => 'tinyint(1) DEFAULT 0',
			'bxgy_settings' => 'longtext',
			'set_discount_settings' => 'longtext',
			'created_by' => 'bigint(20) DEFAULT NULL',
			'created_on' => 'datetime DEFAULT CURRENT_TIMESTAMP',
			'modified_by' => 'bigint(20) DEFAULT NULL',
			'modified_on' => 'datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			'customer_conditions' => 'longtext'
		);
		
		foreach ( $columns_to_add as $column => $definition ) {
			$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table LIKE '$column'" );
			if ( empty( $column_exists ) ) {
				$wpdb->query( "ALTER TABLE $table ADD COLUMN $column $definition" );
			}
		}
	}
}