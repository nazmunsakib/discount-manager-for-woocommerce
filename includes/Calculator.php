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
		if ( empty( $rule->filters ) || ( isset( $rule->filters['apply_to'] ) && $rule->filters['apply_to'] === 'all_products' ) ) {
			return true; // No filters or apply to all products
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
		
		// If apply_to is all_products, match all
		if ( isset( $filters['apply_to'] ) && $filters['apply_to'] === 'all_products' ) {
			return true;
		}
		
		// Check specific products
		if ( isset( $filters['apply_to'] ) && $filters['apply_to'] === 'specific_products' ) {
			$selected_products = isset( $filters['selected_products'] ) ? $filters['selected_products'] : array();
			$product_ids = array_map( function( $p ) { return isset( $p['id'] ) ? $p['id'] : $p; }, $selected_products );
			$filter_method = isset( $filters['filter_method'] ) ? $filters['filter_method'] : 'include';
			
			$is_in_list = in_array( $product_id, $product_ids );
			
			if ( $filter_method === 'include' ) {
				return $is_in_list;
			} else {
				return ! $is_in_list;
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
			if ( empty( $rule->filters ) || ( isset( $rule->filters['apply_to'] ) && $rule->filters['apply_to'] === 'all_products' ) || self::item_matches_filters( $item, $rule->filters ) ) {
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
			if ( empty( $rule->filters ) || ( isset( $rule->filters['apply_to'] ) && $rule->filters['apply_to'] === 'all_products' ) || self::item_matches_filters( $item, $rule->filters ) ) {
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
		
		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table;
		if ( ! $table_exists ) {
			return array( 'apply_product_discount_to' => 'biggest_discount' );
		}
		
		$results = $wpdb->get_results( "SELECT option_name, option_value FROM $table", ARRAY_A );
		
		$settings = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				$settings[ $row['option_name'] ] = maybe_unserialize( $row['option_value'] );
			}
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
		$item = array( 'product_id' => $product_id );
		if ( empty( $rule->filters ) || ( isset( $rule->filters['apply_to'] ) && $rule->filters['apply_to'] === 'all_products' ) ) {
			return true;
		}
		
		if ( ! self::item_matches_filters( $item, $rule->filters ) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Get product discount price
	 *
	 * @param int $product_id Product ID.
	 * @param float $original_price Original price.
	 * @param object $product Product object.
	 * @param int $quantity Quantity.
	 * @param array &$applied_rules Reference to track applied rule IDs.
	 * @param array $cart_items Cart items for cumulative calculation.
	 * @return float
	 */
	public static function get_product_discount_price( $product_id, $original_price, $product = null, $quantity = 1, &$applied_rules = array(), $cart_items = null ) {
		if ( ! $original_price || $original_price <= 0 ) {
			return $original_price;
		}
		
		$rules = Rule::get_active_rules();
		if ( empty( $rules ) ) {
			return $original_price;
		}
		
		if ( ! $product ) {
			$product = wc_get_product( $product_id );
		}
		
		$best_discount = 0;
		$best_price = $original_price;
		$settings = self::get_settings();
		$apply_method = $settings['apply_product_discount_to'] ?? 'biggest_discount';
		
		foreach ( $rules as $rule ) {
			if ( self::product_matches_rule( $product_id, $rule ) ) {
				$calculate_from = isset( $settings['calculate_from'] ) ? $settings['calculate_from'] : 'regular_price';
				
				if ( $calculate_from === 'sale_price' && $product->get_sale_price() ) {
					$base_price = $product->get_sale_price();
				} else {
					$base_price = $product->get_regular_price();
				}
				
				if ( ! $base_price ) {
					$base_price = $original_price;
				}
				
				$new_price = $base_price;
				
					// Check if this is a bulk discount rule
				if ( isset( $rule->bulk_ranges ) && is_array( $rule->bulk_ranges ) && count( $rule->bulk_ranges ) > 0 ) {
					$effective_qty = self::get_effective_quantity( $rule, $product_id, $quantity, $cart_items );
					$discount_amount = self::calculate_bulk_discount_for_product( $rule, $effective_qty, $base_price );
					$new_price = $base_price - $discount_amount;
				} else {
					switch ( $rule->discount_type ) {
						case 'percentage':
							$discount_amount = ( $base_price * $rule->discount_value ) / 100;
							$new_price = $base_price - $discount_amount;
							break;
						case 'fixed':
							$new_price = max( 0, $base_price - $rule->discount_value );
							break;
					}
				}
				
				$discount = $base_price - $new_price;
				
				if ( $apply_method === 'first' ) {
					$applied_rules = array( $rule->id );
					return $new_price;
				} elseif ( $apply_method === 'all' ) {
					$best_discount += $discount;
					$applied_rules[] = $rule->id;
				} elseif ( $apply_method === 'biggest_discount' ) {
					if ( $discount > $best_discount ) {
						$best_discount = $discount;
						$best_price = $new_price;
						$applied_rules = array( $rule->id );
					}
				} elseif ( $apply_method === 'lowest_discount' ) {
					if ( $best_discount === 0 || $discount < $best_discount ) {
						$best_discount = $discount;
						$best_price = $new_price;
						$applied_rules = array( $rule->id );
					}
				}
			}
		}
		
		if ( $apply_method === 'all' && $best_discount > 0 ) {
			return max( 0, $base_price - $best_discount );
		}
		
		return $best_price;
	}

	/**
	 * Get effective quantity based on bulk operator
	 *
	 * @param Rule $rule Discount rule.
	 * @param int $product_id Product ID.
	 * @param int $quantity Current quantity.
	 * @param array $cart_items Cart items.
	 * @return int
	 */
	private static function get_effective_quantity( $rule, $product_id, $quantity, $cart_items ) {
		$bulk_operator = isset( $rule->bulk_operator ) ? $rule->bulk_operator : 'product_individual';
		
		if ( $bulk_operator === 'product_cumulative' && $cart_items ) {
			$total_qty = 0;
			foreach ( $cart_items as $item ) {
				if ( self::item_matches_filters( $item, $rule->filters ) ) {
					$total_qty += isset( $item['quantity'] ) ? $item['quantity'] : 1;
				}
			}
			return $total_qty;
		}
		
		return $quantity;
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
		$bulk_ranges = $rule->bulk_ranges;
		if ( is_string( $bulk_ranges ) ) {
			$bulk_ranges = json_decode( $bulk_ranges, true );
		}
		
		if ( empty( $bulk_ranges ) || ! is_array( $bulk_ranges ) ) {
			return 0;
		}
		
		foreach ( $bulk_ranges as $range ) {
			$min = isset( $range['min'] ) ? (int) $range['min'] : 0;
			$max = isset( $range['max'] ) ? (int) $range['max'] : null;
			$discount_type = isset( $range['discount_type'] ) ? $range['discount_type'] : 'percentage';
			$discount_value = isset( $range['discount_value'] ) ? (float) $range['discount_value'] : 0;
			
			if ( $quantity >= $min && ( $max === null || $quantity <= $max ) ) {
				if ( $discount_type === 'percentage' ) {
					return ( $price * $discount_value ) / 100;
				} elseif ( $discount_type === 'fixed_price' ) {
					return max( 0, $price - $discount_value );
				} else {
					return $discount_value;
				}
			}
		}
		
		return 0;
	}

	/**
	 * Get bulk pricing table for product
	 *
	 * @param int $product_id Product ID.
	 * @return array|null
	 */
	public static function get_bulk_pricing_table( $product_id ) {
		$rules = Rule::get_active_rules();
		$product = wc_get_product( $product_id );
		
		if ( ! $product ) {
			return null;
		}
		
		foreach ( $rules as $rule ) {
			if ( isset( $rule->bulk_ranges ) && is_array( $rule->bulk_ranges ) && count( $rule->bulk_ranges ) > 0 && self::product_matches_rule( $product_id, $rule ) ) {
				$bulk_ranges = $rule->bulk_ranges;
				
				if ( is_array( $bulk_ranges ) && count( $bulk_ranges ) > 0 ) {
					$settings = self::get_settings();
					$calculate_from = isset( $settings['calculate_from'] ) ? $settings['calculate_from'] : 'regular_price';
					
					if ( $calculate_from === 'sale_price' && $product->get_sale_price() ) {
						$base_price = $product->get_sale_price();
					} else {
						$base_price = $product->get_regular_price();
					}
					
					return array(
						'ranges' => $bulk_ranges,
						'base_price' => $base_price,
						'rule_title' => $rule->title,
					);
				}
			}
		}
		
		return null;
	}
}