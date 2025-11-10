<?php
/**
 * Discount Rule model
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
 * Rule class
 */
class Rule {

	/**
	 * Rule ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Rule title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Discount type
	 *
	 * @var string
	 */
	public $discount_type;

	/**
	 * Discount value
	 *
	 * @var float
	 */
	public $discount_value;

	/**
	 * Rule conditions
	 *
	 * @var array
	 */
	public $conditions = array();

	/**
	 * Rule filters
	 *
	 * @var array
	 */
	public $filters = array();

	/**
	 * Date from
	 *
	 * @var string
	 */
	public $date_from;

	/**
	 * Date to
	 *
	 * @var string
	 */
	public $date_to;

	/**
	 * Usage limit
	 *
	 * @var int
	 */
	public $usage_limit;

	/**
	 * Usage count
	 *
	 * @var int
	 */
	public $usage_count = 0;

	/**
	 * Priority
	 *
	 * @var int
	 */
	public $priority = 10;

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Rule status
	 *
	 * @var string
	 */
	public $status = 'active';

	/**
	 * Exclusive rule
	 *
	 * @var bool
	 */
	public $exclusive = false;

	/**
	 * Bulk discount ranges
	 *
	 * @var array
	 */
	public $bulk_ranges = array();

	/**
	 * Cart label for bulk discount
	 *
	 * @var string
	 */
	public $cart_label;

	/**
	 * Apply as cart rule
	 *
	 * @var bool
	 */
	public $apply_as_cart_rule = false;

	/**
	 * Bulk operator
	 *
	 * @var string
	 */
	public $bulk_operator = 'product_cumulative';

	/**
	 * Badge settings
	 *
	 * @var array
	 */
	public $badge_settings = array();

	/**
	 * Free shipping
	 *
	 * @var bool
	 */
	public $free_shipping = false;

	/**
	 * Buy X Get Y settings
	 *
	 * @var array
	 */
	public $bxgy_settings = array();

	/**
	 * Set discount settings
	 *
	 * @var array
	 */
	public $set_discount_settings = array();

	/**
	 * Customer conditions
	 *
	 * @var array
	 */
	public $customer_conditions = array();

	/**
	 * Created by user ID
	 *
	 * @var int
	 */
	public $created_by;

	/**
	 * Created date
	 *
	 * @var string
	 */
	public $created_on;

	/**
	 * Modified by user ID
	 *
	 * @var int
	 */
	public $modified_by;

	/**
	 * Modified date
	 *
	 * @var string
	 */
	public $modified_on;

	/**
	 * Constructor
	 *
	 * @param array $data Rule data.
	 */
	public function __construct( $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->populate( $data );
		}
	}

	/**
	 * Populate rule data
	 *
	 * @param array $data Rule data.
	 */
	public function populate( $data ) {
		$this->id = isset( $data['id'] ) ? (int) $data['id'] : 0;
		$this->title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$this->description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
		$this->discount_type = isset( $data['discount_type'] ) ? sanitize_text_field( $data['discount_type'] ) : 'percentage';
		$this->discount_value = isset( $data['discount_value'] ) ? (float) $data['discount_value'] : 0;
		$this->conditions = isset( $data['conditions'] ) ? (is_string( $data['conditions'] ) ? json_decode( $data['conditions'], true ) : $data['conditions']) : array();
		$this->filters = isset( $data['filters'] ) ? (is_string( $data['filters'] ) ? json_decode( $data['filters'], true ) : $data['filters']) : array();
		$this->date_from = isset( $data['date_from'] ) ? $data['date_from'] : '';
		$this->date_to = isset( $data['date_to'] ) ? $data['date_to'] : '';
		$this->usage_limit = isset( $data['usage_limit'] ) ? (int) $data['usage_limit'] : null;
		$this->usage_count = isset( $data['usage_count'] ) ? (int) $data['usage_count'] : 0;
		$this->priority = isset( $data['priority'] ) ? (int) $data['priority'] : 10;
		$this->status = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active';
		$this->exclusive = isset( $data['exclusive'] ) ? (bool) $data['exclusive'] : false;
		// Handle bulk_ranges
		if ( isset( $data['bulk_ranges'] ) && ! empty( $data['bulk_ranges'] ) ) {
			if ( is_string( $data['bulk_ranges'] ) ) {
				$this->bulk_ranges = json_decode( $data['bulk_ranges'], true );
				if ( ! is_array( $this->bulk_ranges ) ) {
					$this->bulk_ranges = array();
				}
			} elseif ( is_array( $data['bulk_ranges'] ) ) {
				$this->bulk_ranges = $data['bulk_ranges'];
			} else {
				$this->bulk_ranges = array();
			}
		} else {
			$this->bulk_ranges = array();
		}
		$this->cart_label = isset( $data['cart_label'] ) ? sanitize_text_field( $data['cart_label'] ) : '';
		$this->apply_as_cart_rule = isset( $data['apply_as_cart_rule'] ) ? (bool) $data['apply_as_cart_rule'] : false;
		$this->bulk_operator = isset( $data['bulk_operator'] ) ? sanitize_text_field( $data['bulk_operator'] ) : 'product_cumulative';
		$this->badge_settings = isset( $data['badge_settings'] ) ? (is_string( $data['badge_settings'] ) ? json_decode( $data['badge_settings'], true ) : $data['badge_settings']) : array();
		$this->free_shipping = isset( $data['free_shipping'] ) ? (bool) $data['free_shipping'] : false;
		$this->bxgy_settings = isset( $data['bxgy_settings'] ) ? (is_string( $data['bxgy_settings'] ) ? json_decode( $data['bxgy_settings'], true ) : $data['bxgy_settings']) : array();
		$this->set_discount_settings = isset( $data['set_discount_settings'] ) ? (is_string( $data['set_discount_settings'] ) ? json_decode( $data['set_discount_settings'], true ) : $data['set_discount_settings']) : array();
		$this->customer_conditions = isset( $data['customer_conditions'] ) ? (is_string( $data['customer_conditions'] ) ? json_decode( $data['customer_conditions'], true ) : $data['customer_conditions']) : array();
		$this->created_by = isset( $data['created_by'] ) ? (int) $data['created_by'] : get_current_user_id();
		$this->created_on = isset( $data['created_on'] ) ? $data['created_on'] : current_time( 'mysql' );
		$this->modified_by = isset( $data['modified_by'] ) ? (int) $data['modified_by'] : get_current_user_id();
		$this->modified_on = isset( $data['modified_on'] ) ? $data['modified_on'] : current_time( 'mysql' );
	}

	/**
	 * Save rule to database
	 *
	 * @return int|false Rule ID on success, false on failure.
	 */
	public function save() {
		global $wpdb;

		$table = $wpdb->prefix . 'dmwoo_rules';
		$data = array(
			'title' => $this->title,
			'description' => $this->description,
			'discount_type' => $this->discount_type,
			'discount_value' => $this->discount_value,
			'conditions' => wp_json_encode( $this->conditions ),
			'filters' => wp_json_encode( $this->filters ),
			'date_from' => $this->date_from ?: null,
			'date_to' => $this->date_to ?: null,
			'usage_limit' => $this->usage_limit ?: null,
			'usage_count' => $this->usage_count,
			'priority' => $this->priority,
			'status' => $this->status,
			'exclusive' => $this->exclusive ? 1 : 0,
			'bulk_ranges' => wp_json_encode( $this->bulk_ranges ),
			'cart_label' => $this->cart_label,
			'apply_as_cart_rule' => $this->apply_as_cart_rule ? 1 : 0,
			'bulk_operator' => $this->bulk_operator,
			'badge_settings' => wp_json_encode( $this->badge_settings ),
			'free_shipping' => $this->free_shipping ? 1 : 0,
			'bxgy_settings' => wp_json_encode( $this->bxgy_settings ),
			'set_discount_settings' => wp_json_encode( $this->set_discount_settings ),
			'customer_conditions' => wp_json_encode( $this->customer_conditions ),
			'created_by' => $this->created_by,
			'created_on' => $this->created_on,
			'modified_by' => get_current_user_id(),
			'modified_on' => current_time( 'mysql' ),
		);

		if ( $this->id ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $this->id ) );
			if ( $result === false ) {
				error_log( 'DMWOO Rule Update Error: ' . $wpdb->last_error );
				return false;
			}
			return $this->id;
		} else {
			$result = $wpdb->insert( $table, $data );
			if ( $result === false ) {
				error_log( 'DMWOO Rule Insert Error: ' . $wpdb->last_error );
				return false;
			}
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
	}

	/**
	 * Get rule by ID
	 *
	 * @param int $id Rule ID.
	 * @return Rule|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'dmwoo_rules';
		$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );

		return $data ? new self( $data ) : null;
	}

	/**
	 * Get all active rules
	 *
	 * @return array
	 */
	public static function get_active_rules() {
		global $wpdb;

		$table = $wpdb->prefix . 'dmwoo_rules';
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active' ORDER BY priority ASC", ARRAY_A );

		$rules = array();
		foreach ( $results as $data ) {
			$rule = new self( $data );
			// Check usage limit
			if ( $rule->usage_limit && $rule->usage_count >= $rule->usage_limit ) {
				continue; // Skip rules that have reached usage limit
			}
			$rules[] = $rule;
		}

		return $rules;
	}

	/**
	 * Increment usage count
	 *
	 * @return bool
	 */
	public function increment_usage() {
		if ( ! $this->id ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_rules';
		
		return $wpdb->query( $wpdb->prepare(
			"UPDATE $table SET usage_count = usage_count + 1 WHERE id = %d",
			$this->id
		) ) !== false;
	}

	/**
	 * Delete rule
	 *
	 * @return bool
	 */
	public function delete() {
		if ( ! $this->id ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'dmwoo_rules';
		
		return $wpdb->delete( $table, array( 'id' => $this->id ) ) !== false;
	}
}