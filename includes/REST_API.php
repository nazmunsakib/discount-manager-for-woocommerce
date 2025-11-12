<?php
/**
 * REST API endpoints
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
 * REST_API class
 */
class REST_API {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route( 'dmwoo/v1', '/rules', array(
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_rules' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'create_rule' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( 'dmwoo/v1', '/rules/(?P<id>\d+)', array(
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_rule' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods' => 'PUT',
				'callback' => array( $this, 'update_rule' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods' => 'DELETE',
				'callback' => array( $this, 'delete_rule' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( 'dmwoo/v1', '/products', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_products' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );

		register_rest_route( 'dmwoo/v1', '/categories', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_categories' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );

		register_rest_route( 'dmwoo/v1', '/rules/(?P<id>\d+)/duplicate', array(
			'methods' => 'POST',
			'callback' => array( $this, 'duplicate_rule' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );

		register_rest_route( 'dmwoo/v1', '/customers', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_customers' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );

		register_rest_route( 'dmwoo/v1', '/settings', array(
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'save_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( 'dmwoo/v1', '/settings/reset', array(
			'methods' => 'POST',
			'callback' => array( $this, 'reset_settings' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}

	/**
	 * Check permissions
	 */
	public function check_permissions() {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Get all rules
	 */
	public function get_rules( $request ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'dmwoo_rules';
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}dmwoo_rules ORDER BY priority ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		
		foreach ( $results as &$rule ) {
			$rule['conditions'] = json_decode( $rule['conditions'], true );
			$rule['filters'] = json_decode( $rule['filters'], true );
		}
		
		return rest_ensure_response( $results );
	}

	/**
	 * Get single rule
	 */
	public function get_rule( $request ) {
		$rule = Rule::get( $request['id'] );
		
		if ( ! $rule ) {
			return new \WP_Error( 'rule_not_found', 'Rule not found', array( 'status' => 404 ) );
		}
		
		return rest_ensure_response( array(
			'id' => $rule->id,
			'title' => $rule->title,
			'discount_type' => $rule->discount_type,
			'discount_value' => $rule->discount_value,
			'conditions' => $rule->conditions,
			'filters' => $rule->filters,
			'status' => $rule->status,
		) );
	}

	/**
	 * Create rule
	 */
	public function create_rule( $request ) {
		$params = $request->get_json_params();
		
		$rule = new Rule();
		$rule->title = sanitize_text_field( $params['title'] );
		$rule->discount_type = sanitize_text_field( $params['discount_type'] );
		$rule->discount_value = floatval( $params['discount_value'] );
		$rule->conditions = $params['conditions'] ?? array();
		$rule->filters = $params['filters'] ?? array();
		$rule->customer_conditions = $params['customer_conditions'] ?? array();
		$rule->status = sanitize_text_field( $params['status'] ?? 'active' );
		$rule->bulk_ranges = $params['bulk_ranges'] ?? '';
		$rule->bulk_operator = sanitize_text_field( $params['bulk_operator'] ?? 'product_individual' );
		$rule->apply_as_cart_rule = intval( $params['apply_as_cart_rule'] ?? 0 );
		$rule->cart_label = sanitize_text_field( $params['cart_label'] ?? '' );
		
		$rule_id = $rule->save();
		
		if ( $rule_id ) {
			return rest_ensure_response( array( 'id' => $rule_id, 'message' => 'Rule created successfully' ) );
		}
		
		return new \WP_Error( 'create_failed', 'Failed to create rule', array( 'status' => 500 ) );
	}

	/**
	 * Update rule
	 */
	public function update_rule( $request ) {
		try {
			$rule = Rule::get( $request['id'] );
			
			if ( ! $rule ) {
				return new \WP_Error( 'rule_not_found', 'Rule not found', array( 'status' => 404 ) );
			}
			
			$params = $request->get_json_params();
			
			if ( isset( $params['title'] ) ) $rule->title = sanitize_text_field( $params['title'] );
			if ( isset( $params['description'] ) ) $rule->description = sanitize_textarea_field( $params['description'] );
			if ( isset( $params['discount_type'] ) ) $rule->discount_type = sanitize_text_field( $params['discount_type'] );
			if ( isset( $params['discount_value'] ) ) $rule->discount_value = floatval( $params['discount_value'] );
			if ( isset( $params['conditions'] ) ) $rule->conditions = $params['conditions'];
			if ( isset( $params['filters'] ) ) $rule->filters = $params['filters'];
			if ( isset( $params['customer_conditions'] ) ) $rule->customer_conditions = $params['customer_conditions'];
			if ( isset( $params['usage_limit'] ) ) $rule->usage_limit = $params['usage_limit'] ? intval( $params['usage_limit'] ) : null;
			if ( isset( $params['priority'] ) ) $rule->priority = intval( $params['priority'] );
			if ( isset( $params['status'] ) ) $rule->status = sanitize_text_field( $params['status'] );
			if ( isset( $params['bulk_ranges'] ) ) $rule->bulk_ranges = $params['bulk_ranges'];
			if ( isset( $params['bulk_operator'] ) ) $rule->bulk_operator = sanitize_text_field( $params['bulk_operator'] );
			if ( isset( $params['apply_as_cart_rule'] ) ) $rule->apply_as_cart_rule = intval( $params['apply_as_cart_rule'] );
			if ( isset( $params['cart_label'] ) ) $rule->cart_label = sanitize_text_field( $params['cart_label'] );
			
			if ( $rule->save() ) {
				return rest_ensure_response( array( 'message' => 'Rule updated successfully' ) );
			}
			
			return new \WP_Error( 'update_failed', 'Failed to update rule', array( 'status' => 500 ) );
			
		} catch ( Exception $e ) {
			return new \WP_Error( 'update_error', $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Delete rule
	 */
	public function delete_rule( $request ) {
		$rule = Rule::get( $request['id'] );
		
		if ( ! $rule ) {
			return new \WP_Error( 'rule_not_found', 'Rule not found', array( 'status' => 404 ) );
		}
		
		if ( $rule->delete() ) {
			return rest_ensure_response( array( 'message' => 'Rule deleted successfully' ) );
		}
		
		return new \WP_Error( 'delete_failed', 'Failed to delete rule', array( 'status' => 500 ) );
	}

	/**
	 * Get products for select
	 */
	public function get_products( $request ) {
		$search = $request->get_param( 'search' );
		
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 10,
			'post_status' => 'publish',
		);
		
		if ( $search ) {
			$args['s'] = $search;
		}
		
		$products = get_posts( $args );
		$result = array();
		
		foreach ( $products as $product_post ) {
			$product = wc_get_product( $product_post->ID );
			if ( $product ) {
				$price = $product->get_price();
				$regular_price = $product->get_regular_price();
				$sale_price = $product->get_sale_price();
				
				$price_text = '';
				if ( $sale_price && $sale_price < $regular_price ) {
					$price_text = wc_price( $sale_price ) . ' (was ' . wc_price( $regular_price ) . ')';
				} elseif ( $price ) {
					$price_text = wc_price( $price );
				} else {
					$price_text = __( 'Price not set', 'discount-manager-woocommerce' );
				}
				
				$result[] = array(
					'id' => $product->get_id(),
					'name' => $product->get_name(),
					'price' => wp_strip_all_tags( $price_text ),
					'sku' => $product->get_sku(),
				);
			}
		}
		
		return rest_ensure_response( $result );
	}

	/**
	 * Get categories for select
	 */
	public function get_categories( $request ) {
		$categories = get_terms( array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		) );
		
		$result = array();
		
		foreach ( $categories as $category ) {
			$result[] = array(
				'id' => $category->term_id,
				'name' => $category->name,
			);
		}
		
		return rest_ensure_response( $result );
	}

	/**
	 * Duplicate rule
	 */
	public function duplicate_rule( $request ) {
		$original_rule = Rule::get( $request['id'] );
		
		if ( ! $original_rule ) {
			return new \WP_Error( 'rule_not_found', 'Rule not found', array( 'status' => 404 ) );
		}
		
		$new_rule = new Rule();
		$new_rule->title = $original_rule->title . ' (Copy)';
		$new_rule->discount_type = $original_rule->discount_type;
		$new_rule->discount_value = $original_rule->discount_value;
		$new_rule->conditions = $original_rule->conditions;
		$new_rule->filters = $original_rule->filters;
		$new_rule->status = 'inactive';
		
		$rule_id = $new_rule->save();
		
		if ( $rule_id ) {
			return rest_ensure_response( array( 'id' => $rule_id, 'message' => 'Rule duplicated successfully' ) );
		}
		
		return new \WP_Error( 'duplicate_failed', 'Failed to duplicate rule', array( 'status' => 500 ) );
	}

	/**
	 * Get customers for select
	 */
	public function get_customers( $request ) {
		$search = $request->get_param( 'search' );
		
		$args = array(
			'number' => 10,
			'orderby' => 'display_name',
			'order' => 'ASC',
		);
		
		if ( $search ) {
			$args['search'] = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}
		
		$users = get_users( $args );
		$result = array();
		
		foreach ( $users as $user ) {
			$result[] = array(
				'id' => $user->ID,
				'name' => $user->display_name,
				'email' => $user->user_email,
			);
		}
		
		return rest_ensure_response( $result );
	}

	/**
	 * Get settings
	 */
	public function get_settings( $request ) {
		$settings = Settings::get_all();
		return rest_ensure_response( $settings );
	}

	/**
	 * Save settings
	 */
	public function save_settings( $request ) {
		$params = $request->get_json_params();
		
		foreach ( $params as $key => $value ) {
			Settings::set( $key, $value );
		}
		
		return rest_ensure_response( array( 'message' => 'Settings saved successfully' ) );
	}

	/**
	 * Reset settings to defaults
	 */
	public function reset_settings( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_settings';
		
		// Clear all settings
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}dmwoo_settings" );
		
		// Reinitialize defaults
		Settings::init_defaults();
		
		return rest_ensure_response( array( 'message' => 'Settings reset to defaults successfully' ) );
	}
}