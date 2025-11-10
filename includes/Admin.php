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
		add_submenu_page(
			'woocommerce',
			__( 'Discount Manager', 'discount-manager-woocommerce' ),
			__( 'Discount Manager', 'discount-manager-woocommerce' ),
			'manage_options',
			'discount-manager',
			array( $this, 'admin_page' )
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
			<div class="dmwoo-page-header">
				<svg class="dmwoo-header-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
					<path d="M9 9V6C9 4.34315 10.3431 3 12 3C13.6569 3 15 4.34315 15 6V9" stroke="white" stroke-width="2" stroke-linecap="round"/>
					<path d="M20.1213 8.87868L15.5355 13.4645C15.1547 13.8453 14.6095 14 14.0503 14H9.94975C9.39052 14 8.84533 13.8453 8.46447 13.4645L3.87868 8.87868C3.31607 8.31607 3 7.55964 3 6.77817V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V6.77817C21 7.55964 20.6839 8.31607 20.1213 8.87868Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
					<path d="M3 8L3 19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V8" stroke="white" stroke-width="2" stroke-linecap="round"/>
					<circle cx="12" cy="16" r="2" fill="white"/>
					<path d="M9 9H15" stroke="white" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<h1><?php esc_html_e( 'Discount Manager', 'discount-manager-woocommerce' ); ?></h1>
			</div>
			<div id="dmwoo-admin-root">
				<div class="dmwoo-settings-loader">
					<div class="dmwoo-spinner"></div>
				</div>
			</div>
		</div>
		<?php
	}
}