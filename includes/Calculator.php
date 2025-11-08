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
		$settings = self::get_settings();

		// Get applicable rules
		$applicable_rules = array();
		foreach ( $rules as $rule ) {
			if ( self::is_rule_applicable( $rule, $cart_items ) ) {
				$discount = self::calculate_rule_discount( $rule, $cart_items );
				if ( $discount > 0 ) {
					$applicable_rules[] = array(
						'rule' => $rule,
						'discount_amount' => $discount,
					);
				}
			}
		}

		// Apply rule priority logic
		$apply_method = $settings['apply_product_discount_to'] ?? 'biggest_discount';
		switch ( $apply_method ) {
			case 'biggest_discount':
				if ( ! empty( $applicable_rules ) ) {
					usort( $applicable_rules, function( $a, $b ) {
						return $b['discount_amount'] <=> $a['discount_amount'];
					});
					$applicable_rules = array( $applicable_rules[0] );
				}
				break;
			case 'lowest_discount':
				if ( ! empty( $applicable_rules ) ) {
					usort( $applicable_rules, function( $a, $b ) {
						return $a['discount_amount'] <=> $b['discount_amount'];
					});
					$applicable_rules = array( $applicable_rules[0] );
				}
				break;
			case 'first':
				if ( ! empty( $applicable_rules ) ) {
					$applicable_rules = array( $applicable_rules[0] );
				}
				break;
			case 'all':
				// Keep all applicable rules
				break;
		}

		// Convert to final discount format
		foreach ( $applicable_rules as $item ) {
			$rule = $item['rule'];
			$discounts[] = array(
				'rule_id' => $rule->id,
				'rule_title' => $rule->title,
				'discount_type' => $rule->discount_type,
				'discount_amount' => $item['discount_amount'],
			);
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
			case 'fixed':
				$discount = self::calculate_fixed_discount( $rule, $cart_items );
				break;
			case 'bulk':
				$discount = self::calculate_bulk_discount( $rule, $cart_items );
				break;
			case 'cart_percentage':
				$discount = self::calculate_cart_percentage_discount( $rule, $cart_items );
				break;
			case 'cart_fixed':
				$discount = self::calculate_cart_fixed_discount( $rule, $cart_items );
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

	/**
	 * Calculate fixed discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_fixed_discount( $rule, $cart_items ) {
		return $rule->discount_value;
	}

	/**
	 * Calculate cart percentage discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_cart_percentage_discount( $rule, $cart_items ) {
		$subtotal = self::calculate_cart_subtotal( $cart_items );
		return ( $subtotal * $rule->discount_value ) / 100;
	}

	/**
	 * Calculate cart fixed discount
	 *
	 * @param Rule $rule Discount rule.
	 * @param array $cart_items Cart items.
	 * @return float
	 */
	private static function calculate_cart_fixed_discount( $rule, $cart_items ) {
		return $rule->discount_value;
	}

	/**
	 * Get plugin settings
	 *
	 * @return array
	 */
	private static function get_settings() {
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
	 * Check if product is on sale
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function is_product_on_sale( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}
		
		$rules = Rule::get_active_rules();
		foreach ( $rules as $rule ) {
			if ( self::product_matches_rule( $product_id, $rule ) ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if product matches rule
	 *
	 * @param int $product_id Product ID.
	 * @param Rule $rule Discount rule.
	 * @return bool
	 */
	private static function product_matches_rule( $product_id, $rule ) {
		// Check date conditions
		if ( ! self::check_date_conditions( $rule ) ) {
			return false;
		}
		
		// Check product filters
		if ( ! empty( $rule->filters ) ) {
			$item = array( 'product_id' => $product_id );
			if ( ! self::item_matches_filters( $item, $rule->filters ) ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Get product discount price
	 *
	 * @param int $product_id Product ID.
	 * @param float $original_price Original price.
	 * @param int $quantity Quantity.
	 * @return float
	 */
	public static function get_product_discount_price( $product_id, $original_price, $quantity = 1 ) {
		$rules = Rule::get_active_rules();
		$best_discount = 0;
		$settings = self::get_settings();
		
		foreach ( $rules as $rule ) {
			if ( self::product_matches_rule( $product_id, $rule ) ) {
				$discount = 0;
				
				switch ( $rule->discount_type ) {
					case 'percentage':
						$discount = ( $original_price * $rule->discount_value ) / 100;
						break;
					case 'fixed':
						$discount = $rule->discount_value;
						break;
					case 'bulk':
						$discount = self::calculate_bulk_discount_for_product( $rule, $quantity, $original_price );
						break;
				}
				
				$apply_method = $settings['apply_product_discount_to'] ?? 'biggest_discount';
				if ( $apply_method === 'biggest_discount' && $discount > $best_discount ) {
					$best_discount = $discount;
				} elseif ( $apply_method === 'first' && $best_discount === 0 ) {
					$best_discount = $discount;
					break;
				}
			}
		}
		
		return max( 0, $original_price - $best_discount );
	}

	/**
	 * Calculate bulk discount for single product
	 *
	 * @param Rule $rule Discount rule.
	 * @param int $quantity Quantity.
	 * @param float $price Price.
	 * @return float
	 */
	private static function calculate_bulk_discount_for_product( $rule, $quantity, $price ) {
		$bulk_ranges = $rule->conditions['bulk_ranges'] ?? array();
		
		foreach ( $bulk_ranges as $range ) {
			if ( $quantity >= $range['min'] && ( empty( $range['max'] ) || $quantity <= $range['max'] ) ) {
				return ( $price * $range['discount'] ) / 100;
			}
		}
		
		return 0;
	}
}