<?php
/**
 * Admin class
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */
class WCIFR_Admin {

	/**
	 * Construct
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'wcifr_add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wcifr_register_scripts' ) );

	}


	/**
	 * Scripts and style sheets
	 *
	 * @return void
	 */
	public function wcifr_register_scripts() {

		$screen = get_current_screen();
		if ( 'woocommerce_page_wc-importer-for-reviso' === $screen->id ) {

			/*js*/
			wp_enqueue_script( 'wcifr-js', WCIFR_URI . 'js/wcifr.js', array( 'jquery' ), '1.0', true );

			/*css*/
			wp_enqueue_style( 'bootstrap-iso', plugin_dir_url( __DIR__ ) . 'css/bootstrap-iso.css', array(), '1.0' );

		}

		wp_enqueue_style( 'wcifr-style', WCIFR_URI . 'css/wc-importer-for-reviso.css', array(), '1.0' );

	}


	/**
	 * Menu page
	 *
	 * @return string
	 */
	public function wcifr_add_menu() {

		$wcifr_page = add_submenu_page( 'woocommerce', 'WCIFR Options', 'WC Importer for Reviso', 'manage_woocommerce', 'wc-importer-for-reviso', array( $this, 'wcifr_options' ) );

		return $wcifr_page;

	}


	/**
	 * Options page
	 *
	 * @return mixed
	 */
	public function wcifr_options() {

		/*Right of access*/
		if ( ! current_user_can( 'manage_woocommerce' ) ) {

			wp_die( esc_html( __( 'It seems like you don\'t have permission to see this page', 'wc-importer-for-reviso' ) ) );

		}

		/*Page template start*/
		echo '<div class="wrap">';
			echo '<div class="wrap-left">';

		/*Check if WooCommerce is installed ancd activated*/
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div id="message" class="error">';
				echo '<p>';
					echo '<strong>' . esc_html( __( 'ATTENTION! It seems like Woocommerce is not installed', 'wc-importer-for-reviso' ) ) . '</strong>';
				echo '</p>';
			echo '</div>';
			exit;
		}

				echo '<div id="wcifr-generale">';

					/*Header*/
					echo '<h1 class="wcifr main">' . esc_html( __( 'WooCommerce Importer for Reviso - Premium', 'wc-importer-for-reviso' ) ) . '</h1>';

					/*Plugin options menu*/
					echo '<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div>';
					echo '<h2 id="wcifr-admin-menu" class="nav-tab-wrapper woo-nav-tab-wrapper">';
						echo '<a href="#" data-link="wcifr-settings" class="nav-tab nav-tab-active" onclick="return false;">' . esc_html( __( 'Settings', 'wc-importer-for-reviso' ) ) . '</a>';
						echo '<a href="#" data-link="wcifr-suppliers" class="nav-tab" onclick="return false;">' . esc_html( __( 'Suppliers', 'wc-importer-for-reviso' ) ) . '</a>';
						echo '<a href="#" data-link="wcifr-products" class="nav-tab" onclick="return false;">' . esc_html( __( 'Products', 'wc-importer-for-reviso' ) ) . '</a>';
						echo '<a href="#" data-link="wcifr-customers" class="nav-tab" onclick="return false;">' . esc_html( __( 'Customers', 'wc-importer-for-reviso' ) ) . '</a>';
					echo '</h2>';

					/*Settings*/
					echo '<div id="wcifr-settings" class="wcifr-admin" style="display: block;">';

						include WCIFR_ADMIN . 'wcifr-settings-template.php';

					echo '</div>';

					/*Suppliers*/
					echo '<div id="wcifr-suppliers" class="wcifr-admin">';

						include WCIFR_ADMIN . 'wcifr-suppliers-template.php';

					echo '</div>';

					/*Products*/
					echo '<div id="wcifr-products" class="wcifr-admin">';

						include WCIFR_ADMIN . 'wcifr-products-template.php';

					echo '</div>';

					/*Customers*/
					echo '<div id="wcifr-customers" class="wcifr-admin">';

						include WCIFR_ADMIN . 'wcifr-customers-template.php';

					echo '</div>';

				echo '</div>';

				/*Admin message*/
				echo '<div class="wcifr-message">';
					echo '<div class="yes"></div>';
					echo '<div class="not"></div>';
				echo '</div>';

			echo '</div>';

			echo '<div class="wrap-right"></div>';

			echo '<div class="clear"></div>';

		echo '</div>';

	}

}
new WCIFR_Admin();
