<?php
/**
 * Plugin Name: Discount Manager for WooCommerce
 * Plugin URI: https://github.com/nazmunsakib/discount-manager-for-woocommerce
 * Description: Simple to complex discount rules for your WooCommerce store. Core package.
 * Version: 1.0.0
 * Author: Nazmun Sakib
 * Author URI: https://nazmunsakib.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: discount-manager-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 *
 * @package Discount_Manager
 * @author Nazmun Sakib
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin file constant.
if ( ! defined( 'DMWOO_PLUGIN_FILE' ) ) {
	define( 'DMWOO_PLUGIN_FILE', __FILE__ );
}

// Include Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Include the main class.
require_once plugin_dir_path( __FILE__ ) . 'includes/Discount_Manager.php';

/**
 * Main instance of Dmwoo\Discount_Manager.
 *
 * @return Dmwoo\Discount_Manager
 */
function dmwoo() {
	return Dmwoo\Discount_Manager::instance();
}

// Initialize the plugin.
dmwoo();