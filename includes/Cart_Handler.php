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
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_cart_discounts' ) );
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_savings_message' ) );
	}

	/**
	 * Apply cart discounts
	 */
	public function apply_cart_discounts() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! WC()->cart ) {
			return;
		}

		$cart_items = WC()->cart->get_cart();
		if ( empty( $cart_items ) ) {
			return;
		}

		$discounts = Calculator::calculate_cart_discounts( $cart_items );
		
		foreach ( $discounts as $discount ) {
			WC()->cart->add_fee( 
				sprintf( __( 'Discount: %s', 'discount-manager-woocommerce' ), $discount['rule_title'] ),
				-$discount['discount_amount']
			);
		}

		// Store discounts in session for display
		WC()->session->set( 'dmwoo_applied_discounts', $discounts );
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
}