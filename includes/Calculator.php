<?php
/**
 * Discount Calculator
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
 * Calculator class
 */
class Calculator {

	/**
	 * Calculate discount for cart
	 *
	 * @param array $cart_items Cart items.
	 * @return array
	 */
	public static function calculate_cart_discounts( $cart_items ) {
		$rules = Rule::get_active_rules();
		$discounts = array();

		foreach ( $rules as $rule ) {
			if ( self::is_rule_applicable( $rule, $cart_items ) ) {
				$discount = self::calculate_rule_discount( $rule, $cart_items );
				if ( $discount > 0 ) {
					$discounts[] = array(
						'rule_id' => $rule->id,
						'rule_title' => $rule->title,
						'discount_type' => $rule->discount_type,
						'discount_amount' => $discount,
					);
				}
			}
		}

		return $discounts;
	}

	/**
	 * Check if rule is applicable
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return bool
	 */
	private static function is_rule_applicable( $rule, $cart_items ) {
		// Check date conditions
		if ( ! self::check_date_conditions( $rule ) ) {
			return false;
		}

		// Check cart conditions
		if ( ! self::check_cart_conditions( $rule, $cart_items ) ) {
			return false;
		}

		// Check product filters
		if ( ! self::check_product_filters( $rule, $cart_items ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check date conditions
	 *
	 * @param Rule $rule Discount rule.
	 * @return bool
	 */
	private static function check_date_conditions( $rule ) {
		$current_time = current_time( 'timestamp' );

		// Check start date
		if ( ! empty( $rule->conditions['date_from'] ) ) {
			$start_time = strtotime( $rule->conditions['date_from'] );
			if ( $current_time < $start_time ) {
				return false;
			}
		}

		// Check end date
		if ( ! empty( $rule->conditions['date_to'] ) ) {
			$end_time = strtotime( $rule->conditions['date_to'] );
			if ( $current_time > $end_time ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check cart conditions
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return bool
	 */
	private static function check_cart_conditions( $rule, $cart_items ) {
		// Check minimum subtotal
		if ( ! empty( $rule->conditions['min_subtotal'] ) ) {
			$subtotal = self::calculate_cart_subtotal( $cart_items );
			if ( $subtotal < $rule->conditions['min_subtotal'] ) {
				return false;
			}
		}

		// Check minimum quantity
		if ( ! empty( $rule->conditions['min_quantity'] ) ) {
			$total_quantity = self::calculate_cart_quantity( $cart_items );
			if ( $total_quantity < $rule->conditions['min_quantity'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check product filters
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return bool
	 */
	private static function check_product_filters( $rule, $cart_items ) {
		if ( empty( $rule->filters ) ) {
			return true; // No filters means apply to all products
		}

		// Check if any cart item matches the filters
		foreach ( $cart_items as $item ) {
			if ( self::item_matches_filters( $item, $rule->filters ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if item matches filters
	 *
	 * @param array $item Cart item.
	 * @param array $filters Rule filters.
	 * @return bool
	 */
	private static function item_matches_filters( $item, $filters ) {
		$product_id = $item['product_id'];

		// Check specific products
		if ( ! empty( $filters['products'] ) && in_array( $product_id, $filters['products'] ) ) {
			return true;
		}

		// Check categories
		if ( ! empty( $filters['categories'] ) ) {
			$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
			if ( array_intersect( $filters['categories'], $product_categories ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Calculate rule discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_rule_discount( $rule, $cart_items ) {
		$discount = 0;

		switch ( $rule->discount_type ) {
			case 'percentage':
				$discount = self::calculate_percentage_discount( $rule, $cart_items );
				break;
			case 'bulk':
				$discount = self::calculate_bulk_discount( $rule, $cart_items );
				break;
		}

		return $discount;
	}

	/**
	 * Calculate percentage discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_percentage_discount( $rule, $cart_items ) {
		$subtotal = self::calculate_applicable_subtotal( $rule, $cart_items );
		return ( $subtotal * $rule->discount_value ) / 100;
	}

	/**
	 * Calculate bulk discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_bulk_discount( $rule, $cart_items ) {
		$total_quantity = self::calculate_applicable_quantity( $rule, $cart_items );
		$bulk_ranges = $rule->conditions['bulk_ranges'] ?? array();

		$discount_percentage = 0;
		foreach ( $bulk_ranges as $range ) {
			if ( $total_quantity >= $range['min'] && ( empty( $range['max'] ) || $total_quantity <= $range['max'] ) ) {
				$discount_percentage = $range['discount'];
				break;
			}
		}

		if ( $discount_percentage > 0 ) {
			$subtotal = self::calculate_applicable_subtotal( $rule, $cart_items );
			return ( $subtotal * $discount_percentage ) / 100;
		}

		return 0;
	}

	/**
	 * Calculate cart subtotal
	 *
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_cart_subtotal( $cart_items ) {
		$subtotal = 0;
		foreach ( $cart_items as $item ) {
			$subtotal += $item['line_total'];
		}
		return $subtotal;
	}

	/**
	 * Calculate cart quantity
	 *
	 * @param array $cart_items Cart items.
	 * @return int
	 */
	private static function calculate_cart_quantity( $cart_items ) {
		$quantity = 0;
		foreach ( $cart_items as $item ) {
			$quantity += $item['quantity'];
		}
		return $quantity;
	}

	/**
	 * Calculate applicable subtotal
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_applicable_subtotal( $rule, $cart_items ) {
		$subtotal = 0;
		foreach ( $cart_items as $item ) {
			if ( empty( $rule->filters ) || self::item_matches_filters( $item, $rule->filters ) ) {
				$subtotal += $item['line_total'];
			}
		}
		return $subtotal;
	}

	/**
	 * Calculate applicable quantity
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return int
	 */
	private static function calculate_applicable_quantity( $rule, $cart_items ) {
		$quantity = 0;
		foreach ( $cart_items as $item ) {
			if ( empty( $rule->filters ) || self::item_matches_filters( $item, $rule->filters ) ) {
				$quantity += $item['quantity'];
			}
		}
		return $quantity;
	}
}