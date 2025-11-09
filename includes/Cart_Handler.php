<?php
/**
 * Cart discount handler
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
 * Cart_Handler class
 */
class Cart_Handler {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_cart_discounts' ), 10, 1 );
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_savings_message' ) );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'modify_cart_item_price' ), 10, 3 );
	}

	/**
	 * Apply cart discounts
	 */
	public function apply_cart_discounts( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $product->get_id();
			
			// Get base price based on settings
			$calculate_from = Settings::get( 'calculate_from', 'regular_price' );
			if ( $calculate_from === 'sale_price' && $product->get_sale_price() ) {
				$base_price = $product->get_sale_price();
			} else {
				$base_price = $product->get_regular_price();
			}
			
			$discount_price = Calculator::get_product_discount_price( $product_id, $base_price, $product );
			
			if ( $discount_price < $base_price ) {
				$product->set_price( $discount_price );
			}
		}
	}

	/**
	 * Display savings message
	 */
	public function display_savings_message() {
		$discounts = WC()->session->get( 'dmwoo_applied_discounts', array() );
		
		if ( empty( $discounts ) ) {
			return;
		}

		$total_savings = 0;
		foreach ( $discounts as $discount ) {
			$total_savings += $discount['discount_amount'];
		}

		if ( $total_savings > 0 ) {
			echo '<div class="dmwoo-savings-message">';
			echo '<strong>' . sprintf( 
				__( 'You saved: %s', 'discount-manager-woocommerce' ), 
				wc_price( $total_savings ) 
			) . '</strong>';
			echo '</div>';
		}
	}

	/**
	 * Modify cart item price display
	 *
	 * @param string $price_html Price HTML.
	 * @param array $cart_item Cart item.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function modify_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		return wc_price( $product->get_price() );
	}
}