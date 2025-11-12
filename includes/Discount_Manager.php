<?php
/**
 * Main plugin class
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
 * Main Discount_Manager class
 */
class Discount_Manager {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Single instance of the class
	 *
	 * @var Discount_Manager
	 */
	protected static $_instance = null;

	/**
	 * Main instance
	 *
	 * @return Discount_Manager
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants
	 */
	private function define_constants() {
		$this->define( 'DMWOO_VERSION', $this->version );
		$this->define( 'DMWOO_PLUGIN_BASENAME', plugin_basename( DMWOO_PLUGIN_FILE ) );
		$this->define( 'DMWOO_PLUGIN_PATH', plugin_dir_path( DMWOO_PLUGIN_FILE ) );
		$this->define( 'DMWOO_PLUGIN_URL', plugin_dir_url( DMWOO_PLUGIN_FILE ) );
	}

	/**
	 * Define constant if not already set
	 *
	 * @param string $name Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required files
	 */
	public function includes() {
		include_once DMWOO_PLUGIN_PATH . 'includes/Database.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Settings.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Rule.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Calculator.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Cart_Handler.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Product_Display.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/REST_API.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Enqueue.php';
		include_once DMWOO_PLUGIN_PATH . 'includes/Admin.php';
	}

	/**
	 * Hook into actions and filters
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'check_database' ) );
		register_activation_hook( DMWOO_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( DMWOO_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Init plugin when WordPress initializes
	 */
	public function init() {
		// Initialize REST API
		new REST_API();

		// Initialize enqueue
		new Enqueue();

		// Initialize cart handler
		new Cart_Handler();

		// Initialize product display
		new Product_Display();

		// Initialize admin
		if ( is_admin() ) {
			new Admin();
		}
	}



	/**
	 * Check and repair database if needed
	 */
	public function check_database() {
		$db_version = get_option( 'dmwoo_db_version', '0' );
		
		if ( version_compare( $db_version, $this->version, '<' ) ) {
			Database::create_tables();
			Settings::init_defaults();
			update_option( 'dmwoo_db_version', $this->version );
		}
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		Database::create_tables();
		Settings::init_defaults();
		update_option( 'dmwoo_db_version', $this->version );
		update_option( 'dmwoo_activated', time() );
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Clean up if needed
	}
}