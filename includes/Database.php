<?php
/**
 * Database operations
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
 * Database class
 */
class Database {

	/**
	 * Create plugin tables
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Rules table
		$rules_table = $wpdb->prefix . 'dmwoo_rules';
		$rules_sql = "CREATE TABLE $rules_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description text,
			discount_type varchar(50) NOT NULL DEFAULT 'percentage',
			discount_value decimal(10,2) NOT NULL DEFAULT 0,
			conditions longtext,
			filters longtext,
			date_from datetime DEFAULT NULL,
			date_to datetime DEFAULT NULL,
			usage_limit int(11) DEFAULT NULL,
			usage_count int(11) DEFAULT 0,
			priority int(11) DEFAULT 10,
			status varchar(20) DEFAULT 'active',
			exclusive tinyint(1) DEFAULT 0,
			bulk_ranges longtext,
			cart_label varchar(255) DEFAULT NULL,
			apply_as_cart_rule tinyint(1) DEFAULT 0,
			bulk_operator varchar(50) DEFAULT 'product_cumulative',
			badge_settings longtext,
			free_shipping tinyint(1) DEFAULT 0,
			bxgy_settings longtext,
			set_discount_settings longtext,
			created_by bigint(20) DEFAULT NULL,
			created_on datetime DEFAULT CURRENT_TIMESTAMP,
			modified_by bigint(20) DEFAULT NULL,
			modified_on datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			apply_to varchar(50) DEFAULT 'all_products',
			min_subtotal decimal(10,2) DEFAULT NULL,
			min_quantity int(11) DEFAULT NULL,
			max_uses_per_customer int(11) DEFAULT NULL,
			customer_conditions longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY priority (priority),
			KEY discount_type (discount_type),
			KEY exclusive (exclusive),
			KEY created_by (created_by)
		) $charset_collate;";

		// Usage tracking table
		$usage_table = $wpdb->prefix . 'dmwoo_rule_usage';
		$usage_sql = "CREATE TABLE $usage_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			rule_id bigint(20) NOT NULL,
			order_id bigint(20) NOT NULL,
			customer_id bigint(20) DEFAULT NULL,
			discount_amount decimal(10,2) NOT NULL,
			product_ids text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY rule_id (rule_id),
			KEY order_id (order_id),
			KEY customer_id (customer_id)
		) $charset_collate;";

		// Settings table
		$settings_table = $wpdb->prefix . 'dmwoo_settings';
		$settings_sql = "CREATE TABLE $settings_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			option_name varchar(255) NOT NULL,
			option_value longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY option_name (option_name)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $rules_sql );
		dbDelta( $usage_sql );
		dbDelta( $settings_sql );
	}

	/**
	 * Drop plugin tables
	 */
	public static function drop_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dmwoo_rule_usage" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dmwoo_rules" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dmwoo_settings" );
	}
}