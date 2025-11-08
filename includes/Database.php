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
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY priority (priority)
		) $charset_collate;";

		// Usage tracking table
		$usage_table = $wpdb->prefix . 'dmwoo_rule_usage';
		$usage_sql = "CREATE TABLE $usage_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			rule_id bigint(20) NOT NULL,
			order_id bigint(20) NOT NULL,
			discount_amount decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY rule_id (rule_id),
			KEY order_id (order_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $rules_sql );
		dbDelta( $usage_sql );
	}

	/**
	 * Drop plugin tables
	 */
	public static function drop_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dmwoo_rule_usage" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dmwoo_rules" );
	}
}