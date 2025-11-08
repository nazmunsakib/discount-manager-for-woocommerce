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
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_bulk_discount_table' ), 25 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'modify_price_display' ), 10, 2 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_sale_badge' ) );
	}

	/**
	 * Display bulk discount table
	 */
	public function display_bulk_discount_table() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$rules = Rule::get_active_rules();
		$bulk_rules = array();

		foreach ( $rules as $rule ) {
			if ( $rule->discount_type === 'bulk' && $this->product_matches_rule( $product, $rule ) ) {
				$bulk_rules[] = $rule;
			}
		}

		if ( empty( $bulk_rules ) ) {
			return;
		}

		echo '<div class="dmwoo-bulk-discount-table">';
		echo '<h4>' . __( 'Bulk Discount Pricing', 'discount-manager-woocommerce' ) . '</h4>';
		echo '<table class="dmwoo-discount-table">';
		echo '<thead><tr><th>' . __( 'Quantity', 'discount-manager-woocommerce' ) . '</th><th>' . __( 'Discount', 'discount-manager-woocommerce' ) . '</th><th>' . __( 'Price', 'discount-manager-woocommerce' ) . '</th></tr></thead>';
		echo '<tbody>';

		foreach ( $bulk_rules as $rule ) {
			$bulk_ranges = $rule->conditions['bulk_ranges'] ?? array();
			foreach ( $bulk_ranges as $range ) {
				$original_price = $product->get_price();
				$discounted_price = $original_price * ( 1 - $range['discount'] / 100 );
				
				$quantity_text = $range['min'];
				if ( ! empty( $range['max'] ) ) {
					$quantity_text .= ' - ' . $range['max'];
				} else {
					$quantity_text .= '+';
				}

				echo '<tr>';
				echo '<td>' . esc_html( $quantity_text ) . '</td>';
				echo '<td>' . esc_html( $range['discount'] ) . '%</td>';
				echo '<td>' . wc_price( $discounted_price ) . '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Modify price display with strikethrough
	 *
	 * @param string $price_html Price HTML.
	 * @param object $product Product object.
	 * @return string
	 */
	public function modify_price_display( $price_html, $product ) {
		if ( is_admin() || ! $product ) {
			return $price_html;
		}

		$discount = $this->get_product_discount( $product );
		
		if ( $discount > 0 ) {
			$original_price = $product->get_price();
			$discounted_price = $original_price - $discount;
			
			$price_html = '<del>' . wc_price( $original_price ) . '</del> ';
			$price_html .= '<ins>' . wc_price( $discounted_price ) . '</ins>';
		}

		return $price_html;
	}

	/**
	 * Display sale badge
	 */
	public function display_sale_badge() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$discount = $this->get_product_discount( $product );
		
		if ( $discount > 0 ) {
			$discount_percentage = ( $discount / $product->get_price() ) * 100;
			echo '<span class="dmwoo-sale-badge">' . sprintf( __( '-%s%%', 'discount-manager-woocommerce' ), round( $discount_percentage ) ) . '</span>';
		}
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