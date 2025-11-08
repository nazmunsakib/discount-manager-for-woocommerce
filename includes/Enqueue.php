<?php
/**
 * Enqueue assets
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
 * Enqueue class
 */
class Enqueue {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_scripts( $hook ) {
		// Admin app for discount manager page
		if ( 'toplevel_page_discount-manager' === $hook ) {
			// Enqueue WordPress React dependencies first
			wp_enqueue_script( 'wp-element' );
			wp_enqueue_script( 'wp-components' );
			wp_enqueue_script( 'wp-i18n' );
			wp_enqueue_script( 'wp-api-fetch' );
			
			$this->enqueue_admin_app();
		}
	}

	/**
	 * Enqueue frontend scripts and styles
	 */
	public function frontend_scripts() {
		// Frontend discount functionality
		wp_enqueue_style(
			'dmwoo-frontend',
			DMWOO_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			DMWOO_VERSION
		);

		wp_enqueue_script(
			'dmwoo-frontend',
			DMWOO_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			DMWOO_VERSION,
			true
		);
	}

	/**
	 * Enqueue admin app assets
	 */
	private function enqueue_admin_app() {
		// Enqueue WordPress components CSS
		wp_enqueue_style( 'wp-components' );
		
		wp_enqueue_style(
			'dmwoo-admin-app',
			DMWOO_PLUGIN_URL . 'assets/css/admin-app.css',
			array( 'wp-components' ),
			DMWOO_VERSION
		);

		wp_enqueue_script(
			'dmwoo-admin-app',
			DMWOO_PLUGIN_URL . 'assets/js/admin-clean.js',
			array( 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
			DMWOO_VERSION,
			true
		);

		wp_localize_script(
			'dmwoo-admin-app',
			'dmwooAdmin',
			array(
				'apiUrl' => rest_url( 'dmwoo/v1/' ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
			)
		);

		// Set up API fetch with nonce
		wp_add_inline_script( 'wp-api-fetch', 'wp.apiFetch.use( wp.apiFetch.createNonceMiddleware( "' . wp_create_nonce( 'wp_rest' ) . '" ) );' );
	}
}