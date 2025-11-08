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
	 * Rule status
	 *
	 * @var string
	 */
	public $status = 'active';

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
			'priority' => $this->priority,
			'status' => $this->status,
		);

		if ( $this->id ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $this->id ) );
			return $result !== false ? $this->id : false;
		} else {
			$result = $wpdb->insert( $table, $data );
			if ( $result ) {
				$this->id = $wpdb->insert_id;
				return $this->id;
			}
			return false;
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
			$rules[] = new self( $data );
		}

		return $rules;
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