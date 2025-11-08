<?php
/**
 * Admin functionality
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
 * Admin class
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'remove_notices' ) );
	}

	/**
	 * Add admin menu
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Discount Manager', 'discount-manager-woocommerce' ),
			__( 'Discount Manager', 'discount-manager-woocommerce' ),
			'manage_options',
			'discount-manager',
			array( $this, 'admin_page' ),
			'dashicons-tag',
			56
		);
	}



	/**
	 * Remove admin notices from plugin pages
	 */
	public function remove_notices() {
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'discount-manager' ) !== false ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Admin page content
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Discount Manager', 'discount-manager-woocommerce' ); ?></h1>
			<div id="dmwoo-admin-root">
				<div class="dmwoo-loading">
					<p><?php esc_html_e( 'Loading...', 'discount-manager-woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}