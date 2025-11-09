<?php
/**
 * Product page display features
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
 * Product_Display class
 */
class Product_Display {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Price modifications
		add_filter( 'woocommerce_get_price_html', array( $this, 'modify_price_html' ), 10, 2 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'modify_product_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'modify_product_price' ), 10, 2 );
		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'modify_product_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'modify_product_price' ), 10, 2 );
		
		// Remove sale price to prevent double discounting
		add_filter( 'woocommerce_product_get_sale_price', array( $this, 'remove_sale_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'remove_sale_price' ), 10, 2 );
		
		// Sale badge
		add_filter( 'woocommerce_product_is_on_sale', array( $this, 'modify_on_sale_status' ), 10, 2 );
		add_filter( 'woocommerce_sale_flash', array( $this, 'modify_sale_badge' ), 10, 3 );
		
		// Bulk discount table
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_bulk_discount_table' ) );
		
		// Discount bar
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_discount_bar' ) );
	}

	/**
	 * Display bulk discount table
	 */
	public function display_bulk_discount_table() {
		if ( ! Settings::get( 'show_bulk_table', true ) ) {
			return;
		}
		
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		$product_id = $product->get_id();
		$rules = Rule::get_active_rules();
		$bulk_rules = array();
		
		foreach ( $rules as $rule ) {
			if ( $rule->discount_type === 'bulk' && Calculator::is_product_on_sale( $product_id ) ) {
				$bulk_rules[] = $rule;
			}
		}
		
		if ( empty( $bulk_rules ) ) {
			return;
		}
		
		echo '<div class="dmwoo-bulk-table">';
		echo '<h4>' . esc_html__( 'Bulk Discount', 'discount-manager-woocommerce' ) . '</h4>';
		echo '<table class="dmwoo-discount-table">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Quantity', 'discount-manager-woocommerce' ) . '</th>';
		echo '<th>' . esc_html__( 'Discount', 'discount-manager-woocommerce' ) . '</th>';
		echo '<th>' . esc_html__( 'Price', 'discount-manager-woocommerce' ) . '</th>';
		echo '</tr></thead><tbody>';
		
		foreach ( $bulk_rules as $rule ) {
			$ranges = $rule->conditions['bulk_ranges'] ?? array();
			foreach ( $ranges as $range ) {
				$qty_text = $range['min'];
				if ( ! empty( $range['max'] ) ) {
					$qty_text .= ' - ' . $range['max'];
				} else {
					$qty_text .= '+';
				}
				
				$original_price = $product->get_price();
				$discount_amount = ( $original_price * $range['discount'] ) / 100;
				$discounted_price = $original_price - $discount_amount;
				
				echo '<tr>';
				echo '<td>' . esc_html( $qty_text ) . '</td>';
				echo '<td>' . esc_html( $range['discount'] ) . '%</td>';
				echo '<td>' . wc_price( $discounted_price ) . '</td>';
				echo '</tr>';
			}
		}
		
		echo '</tbody></table></div>';
	}

	/**
	 * Modify price HTML to show strikeout
	 *
	 * @param string $price_html Price HTML.
	 * @param object $product Product object.
	 * @return string
	 */
	public function modify_price_html( $price_html, $product ) {
		if ( ! Settings::get( 'show_strikeout', true ) ) {
			return $price_html;
		}
		
		$product_id = $product->get_id();
		
		// Get base price based on settings
		$calculate_from = Settings::get( 'calculate_from', 'regular_price' );
		if ( $calculate_from === 'sale_price' && $product->get_sale_price() ) {
			$base_price = $product->get_sale_price();
		} else {
			$base_price = $product->get_regular_price();
		}
		
		if ( ! $base_price ) {
			return $price_html;
		}
		
		$discount_price = Calculator::get_product_discount_price( $product_id, $base_price, $product );
		
		if ( $discount_price < $base_price ) {
			$original_html = wc_price( $base_price );
			$discount_html = wc_price( $discount_price );
			
			return '<del>' . $original_html . '</del> <ins>' . $discount_html . '</ins>';
		}
		
		return $price_html;
	}

	/**
	 * Remove sale price to prevent double discounting
	 *
	 * @param float $sale_price Sale price.
	 * @param object $product Product object.
	 * @return float
	 */
	public function remove_sale_price( $sale_price, $product ) {
		return $sale_price;
	}

	/**
	 * Modify product price
	 *
	 * @param float $price Product price.
	 * @param object $product Product object.
	 * @return float
	 */
	public function modify_product_price( $price, $product ) {
		return $price;
	}

	/**
	 * Modify on sale status
	 *
	 * @param bool $on_sale On sale status.
	 * @param object $product Product object.
	 * @return bool
	 */
	public function modify_on_sale_status( $on_sale, $product ) {
		$badge_setting = Settings::get( 'show_sale_badge', 'disabled' );
		$product_id = $product->get_id();
		
		if ( $badge_setting === 'disabled' ) {
			return false;
		}
		
		if ( $badge_setting === 'when_condition_matches' ) {
			return Calculator::is_product_on_sale( $product_id );
		}
		
		if ( $badge_setting === 'at_least_has_any_rules' ) {
			return Calculator::is_product_on_sale( $product_id );
		}
		
		return $on_sale;
	}

	/**
	 * Modify sale badge
	 *
	 * @param string $html Badge HTML.
	 * @param object $post Post object.
	 * @param object $product Product object.
	 * @return string
	 */
	public function modify_sale_badge( $html, $post, $product ) {
		$badge_setting = Settings::get( 'show_sale_badge', 'disabled' );
		$product_id = $product->get_id();
		
		if ( $badge_setting === 'disabled' ) {
			return '';
		}
		
		if ( $badge_setting === 'when_condition_matches' ) {
			if ( Calculator::is_product_on_sale( $product_id ) ) {
				return '<span class="onsale">' . esc_html__( 'Sale!', 'discount-manager-woocommerce' ) . '</span>';
			}
			return '';
		}
		
		if ( $badge_setting === 'at_least_has_any_rules' ) {
			if ( Calculator::is_product_on_sale( $product_id ) ) {
				return '<span class="onsale">' . esc_html__( 'Sale!', 'discount-manager-woocommerce' ) . '</span>';
			}
			return '';
		}
		
		return $html;
	}

	/**
	 * Display discount bar
	 */
	public function display_discount_bar() {
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		$product_id = $product->get_id();
		
		if ( ! Calculator::is_product_on_sale( $product_id ) ) {
			return;
		}
		
		echo '<div class="dmwoo-discount-bar">';
		echo '<span class="dmwoo-discount-text">' . esc_html__( 'Special Discount Available!', 'discount-manager-woocommerce' ) . '</span>';
		echo '</div>';
	}

	/**
	 * Check if product matches rule
	 *
	 * @param object $product Product object.
	 * @param Rule $rule Discount rule.
	 * @return bool
	 */
	private function product_matches_rule( $product, $rule ) {
		if ( empty( $rule->filters ) ) {
			return true;
		}

		$product_id = $product->get_id();

		// Check specific products
		if ( ! empty( $rule->filters['products'] ) && in_array( $product_id, $rule->filters['products'] ) ) {
			return true;
		}

		// Check categories
		if ( ! empty( $rule->filters['categories'] ) ) {
			$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
			if ( array_intersect( $rule->filters['categories'], $product_categories ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get product discount amount
	 *
	 * @param object $product Product object.
	 * @return float
	 */
	private function get_product_discount( $product ) {
		$rules = Rule::get_active_rules();
		$discount = 0;

		foreach ( $rules as $rule ) {
			if ( $rule->discount_type === 'percentage' && $this->product_matches_rule( $product, $rule ) ) {
				$product_discount = ( $product->get_price() * $rule->discount_value ) / 100;
				$discount = max( $discount, $product_discount );
			}
		}

		return $discount;
	}
}