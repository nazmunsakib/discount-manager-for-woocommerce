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
		$results = $wpdb->get_results( "SELECT * FROM $table ORDER BY priority ASC", ARRAY_A );
		
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
		$rule->status = sanitize_text_field( $params['status'] ?? 'active' );
		
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
		$rule = Rule::get( $request['id'] );
		
		if ( ! $rule ) {
			return new \WP_Error( 'rule_not_found', 'Rule not found', array( 'status' => 404 ) );
		}
		
		$params = $request->get_json_params();
		
		$rule->title = sanitize_text_field( $params['title'] );
		$rule->discount_type = sanitize_text_field( $params['discount_type'] );
		$rule->discount_value = floatval( $params['discount_value'] );
		$rule->conditions = $params['conditions'] ?? array();
		$rule->filters = $params['filters'] ?? array();
		$rule->status = sanitize_text_field( $params['status'] ?? 'active' );
		
		if ( $rule->save() ) {
			return rest_ensure_response( array( 'message' => 'Rule updated successfully' ) );
		}
		
		return new \WP_Error( 'update_failed', 'Failed to update rule', array( 'status' => 500 ) );
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
			'posts_per_page' => 20,
			'post_status' => 'publish',
		);
		
		if ( $search ) {
			$args['s'] = $search;
		}
		
		$products = get_posts( $args );
		$result = array();
		
		foreach ( $products as $product ) {
			$result[] = array(
				'id' => $product->ID,
				'title' => $product->post_title,
			);
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
}