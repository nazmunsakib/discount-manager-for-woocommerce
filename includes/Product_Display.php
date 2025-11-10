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
		

		
		// Discount bar
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_discount_bar' ) );
		
		// Bulk pricing table
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_bulk_pricing_table' ) );
	}



	/**
	 * Modify price HTML to show strikeout
	 *
	 * @param string $price_html Price HTML.
	 * @param object $product Product object.
	 * @return string
	 */
	public function modify_price_html( $price_html, $product ) {
		$product_id = $product->get_id();
		$show_strikeout = Settings::get( 'show_strikeout', 1 );
		
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
			if ( $show_strikeout == 1 || $show_strikeout === true ) {
				return '<del>' . wc_price( $base_price ) . '</del> <ins>' . wc_price( $discount_price ) . '</ins>';
			} else {
				return wc_price( $discount_price );
			}
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
	 * Display bulk pricing table
	 */
	public function display_bulk_pricing_table() {
		global $product;
		
		$show_bulk_table = Settings::get( 'show_bulk_table', 1 );
		if ( $show_bulk_table != 1 && $show_bulk_table !== true ) {
			return;
		}
		
		if ( ! $product ) {
			return;
		}
		
		$product_id = $product->get_id();
		$bulk_data = Calculator::get_bulk_pricing_table( $product_id );
		
		if ( ! $bulk_data || empty( $bulk_data['ranges'] ) ) {
			return;
		}
		
		$ranges = $bulk_data['ranges'];
		$base_price = $bulk_data['base_price'];
		
		echo '<div class="dmwoo-bulk-pricing-table">';
		echo '<h4>' . esc_html__( 'Bulk Pricing', 'discount-manager-woocommerce' ) . '</h4>';
		echo '<table>';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Quantity', 'discount-manager-woocommerce' ) . '</th>';
		echo '<th>' . esc_html__( 'Discount', 'discount-manager-woocommerce' ) . '</th>';
		echo '<th>' . esc_html__( 'Price', 'discount-manager-woocommerce' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		
		foreach ( $ranges as $range ) {
			$min = isset( $range['min'] ) ? (int) $range['min'] : 0;
			$max = isset( $range['max'] ) ? (int) $range['max'] : null;
			$discount_type = isset( $range['discount_type'] ) ? $range['discount_type'] : 'percentage';
			$discount_value = isset( $range['discount_value'] ) ? (float) $range['discount_value'] : 0;
			$label = isset( $range['label'] ) && ! empty( $range['label'] ) ? $range['label'] : '';
			
			$qty_text = $min . ( $max ? ' - ' . $max : '+' );
			if ( $label ) {
				$qty_text = $label . ' (' . $qty_text . ')';
			}
			
			if ( $discount_type === 'percentage' ) {
				$discount_text = $discount_value . '% ' . esc_html__( 'off', 'discount-manager-woocommerce' );
				$final_price = $base_price - ( ( $base_price * $discount_value ) / 100 );
			} elseif ( $discount_type === 'fixed_price' ) {
				$discount_text = esc_html__( 'Fixed price', 'discount-manager-woocommerce' );
				$final_price = $discount_value;
			} else {
				$discount_text = wc_price( $discount_value ) . ' ' . esc_html__( 'off', 'discount-manager-woocommerce' );
				$final_price = $base_price - $discount_value;
			}
			
			echo '<tr>';
			echo '<td>' . esc_html( $qty_text ) . '</td>';
			echo '<td>' . wp_kses_post( $discount_text ) . '</td>';
			echo '<td>' . wp_kses_post( wc_price( max( 0, $final_price ) ) ) . '</td>';
			echo '</tr>';
		}
		
		echo '</tbody></table>';
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