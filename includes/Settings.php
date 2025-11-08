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
		
		$value = $wpdb->get_var( $wpdb->prepare(
			"SELECT option_value FROM $table WHERE option_name = %s",
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
		
		$result = $wpdb->replace(
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
		
		$results = $wpdb->get_results( "SELECT option_name, option_value FROM $table", ARRAY_A );
		
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
			// General Settings
			'calculate_discount_from' => 'sale_price',
			'apply_product_discount_to' => 'biggest_discount',
			'apply_discount_subsequently' => false,
			'disable_coupon_when_rule_applied' => 'run_both',
			'refresh_order_review' => false,
			'suppress_other_discount_plugins' => false,
			'compress_css_and_js' => false,
			
			// Product Settings
			'show_on_sale_badge' => 'disabled',
			'customize_on_sale_badge' => false,
			'force_override_on_sale_badge' => false,
			'display_percentage_on_sale_badge' => false,
			'on_sale_badge_html' => '<span class="onsale">Sale!</span>',
			'on_sale_badge_percentage_html' => '<span class="onsale">{{percentage}}%</span>',
			'show_bulk_table' => true,
			'position_to_show_bulk_table' => 'woocommerce_before_add_to_cart_form',
			'position_to_show_discount_bar' => 'woocommerce_before_add_to_cart_form',
			'modify_price_at_shop_page' => true,
			'modify_price_at_product_page' => true,
			'modify_price_at_category_page' => true,
			'show_strikeout_when' => 'show_when_matched',
			
			// Cart Settings
			'show_strikeout_on_cart' => true,
			'apply_cart_discount_as' => 'coupon',
			'combine_all_cart_discounts' => false,
			'discount_label_for_combined_discounts' => 'Cart discount',
			
			// Promotion Settings
			'show_subtotal_promotion' => false,
			'show_cart_quantity_promotion' => false,
			'show_promo_text' => array(),
			'display_saving_text' => 'disabled',
			'you_saved_text' => 'You saved {{total_discount}}',
			'show_applied_rules_message_on_cart' => false,
			'applied_rule_message' => 'Discount <strong>{{title}}</strong> has been applied to your cart.',
			
			// Bulk Table Customization
			'table_column_header' => true,
			'table_title_column' => true,
			'table_title_column_name' => 'Title',
			'table_discount_column' => true,
			'table_discount_column_name' => 'Discount',
			'table_range_column' => true,
			'table_range_column_name' => 'Range',
			'table_discount_column_value' => true,
			
			// Advanced Options
			'wdr_override_custom_price' => false,
			'disable_recalculate_total' => false,
			'disable_recalculate_total_when_coupon_apply' => false,
			
			// Legacy settings
			'enabled' => true,
			'coupon_behavior' => 'run_both',
			'show_sale_badge' => 'disabled',
			'show_strikeout' => true,
			'show_savings' => 'disabled',
			'show_cart_notifications' => false,
			'suppress_third_party' => false,
			'refresh_checkout' => false,
			'combine_cart_discounts' => false,
			'discount_table_position' => 'woocommerce_before_add_to_cart_form',
			'discount_bar_position' => 'woocommerce_before_add_to_cart_form',
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