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
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_cart_adjustments' ), 10, 1 );
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_savings_message' ) );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'modify_cart_item_price' ), 10, 3 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'increment_rule_usage' ), 10, 1 );
		add_filter( 'woocommerce_coupons_enabled', array( $this, 'maybe_disable_coupons' ), 10, 1 );
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

		// Check coupon behavior setting
		$coupon_behavior = Settings::get( 'coupon_behavior', 'run_both' );
		$has_coupons = ! empty( $cart->get_applied_coupons() );
		
		// If disable_rules when coupons applied, skip discount rules
		if ( $coupon_behavior === 'disable_rules' && $has_coupons ) {
			return;
		}

		$applied_rules = array();
		
		$cart_items = $cart->get_cart();
		
		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $product->get_id();
			$quantity = $cart_item['quantity'];
			
			// Get base price based on settings
			$calculate_from = Settings::get( 'calculate_from', 'regular_price' );
			if ( $calculate_from === 'sale_price' && $product->get_sale_price() ) {
				$base_price = $product->get_sale_price();
			} else {
				$base_price = $product->get_regular_price();
			}
			
			// Skip product price adjustment if rule is cart adjustment type
			$rules = Rule::get_active_rules();
			$is_cart_adjustment = false;
			foreach ( $rules as $rule ) {
				if ( $rule->apply_as_cart_rule == 1 && Calculator::item_matches_filters( $cart_item, $rule->filters ) ) {
					$is_cart_adjustment = true;
					break;
				}
			}
			
			if ( $is_cart_adjustment ) {
				continue;
			}
			
			$discount_price = Calculator::get_product_discount_price( $product_id, $base_price, $product, $quantity, $applied_rules, $cart_items );
			
			if ( $discount_price < $base_price ) {
				$product->set_price( $discount_price );
			}
		}
		
		// Store applied rules for usage tracking on order completion
		WC()->session->set( 'dmwoo_applied_rule_ids', array_unique( $applied_rules ) );
		WC()->session->set( 'dmwoo_has_discount_rules', ! empty( $applied_rules ) );
	}

	/**
	 * Apply cart adjustments as fees
	 */
	public function apply_cart_adjustments( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		
		$rules = Rule::get_active_rules();
		$cart_items = $cart->get_cart();
		
		foreach ( $rules as $rule ) {
			if ( $rule->apply_as_cart_rule != 1 ) {
				continue;
			}
			
			// Check if rule matches cart items
			$matches = false;
			foreach ( $cart_items as $item ) {
				if ( Calculator::item_matches_filters( $item, $rule->filters ) ) {
					$matches = true;
					break;
				}
			}
			
			if ( ! $matches ) {
				continue;
			}
			
			// Calculate discount
			$subtotal = 0;
			foreach ( $cart_items as $item ) {
				if ( Calculator::item_matches_filters( $item, $rule->filters ) ) {
					$subtotal += $item['line_total'];
				}
			}
			
			if ( $subtotal <= 0 ) {
				continue;
			}
			
			$discount = 0;
			if ( $rule->discount_type === 'percentage' ) {
				$discount = ( $subtotal * $rule->discount_value ) / 100;
			} else {
				$discount = $rule->discount_value;
			}
			
			if ( $discount > 0 ) {
				$label = ! empty( $rule->cart_label ) ? $rule->cart_label : $rule->title;
				$cart->add_fee( $label, -$discount, false );
			}
			}
	}

	/**
	 * Display savings message
	 */
	public function display_savings_message() {
		// Removed - feature not implemented
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

	/**
	 * Increment usage count for applied rules on order completion
	 *
	 * @param int $order_id Order ID.
	 */
	public function increment_rule_usage( $order_id ) {
		$applied_rule_ids = WC()->session->get( 'dmwoo_applied_rule_ids', array() );
		
		if ( empty( $applied_rule_ids ) ) {
			return;
		}
		
		foreach ( $applied_rule_ids as $rule_id ) {
			$rule = Rule::get( $rule_id );
			if ( $rule ) {
				$rule->increment_usage();
			}
		}
		
		// Clear session
		WC()->session->set( 'dmwoo_applied_rule_ids', array() );
		WC()->session->set( 'dmwoo_has_discount_rules', false );
	}

	/**
	 * Maybe disable coupons based on coupon behavior setting
	 *
	 * @param bool $enabled Whether coupons are enabled.
	 * @return bool
	 */
	public function maybe_disable_coupons( $enabled ) {
		if ( ! WC()->session ) {
			return $enabled;
		}
		
		$coupon_behavior = Settings::get( 'coupon_behavior', 'run_both' );
		
		if ( $coupon_behavior === 'disable_coupon' ) {
			$has_discount_rules = WC()->session->get( 'dmwoo_has_discount_rules', false );
			if ( $has_discount_rules ) {
				return false;
			}
		}
		
		return $enabled;
	}
}