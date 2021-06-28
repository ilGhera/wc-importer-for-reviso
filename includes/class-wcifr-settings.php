<?php
/**
 * General settings
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/includes
 * @since 0.9.0
 */
class WCIFR_Settings {

	/**
	 * Class constructor
	 *
	 * @param boolean $init fire hooks if true.
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'wp_ajax_wcifr-check-connection', array( $this, 'check_connection_callback' ) );
			add_action( 'wp_ajax_wcifr-disconnect', array( $this, 'disconnect_callback' ) );
			add_action( 'admin_footer', array( $this, 'save_agt' ) );

		}

		$this->wcifr_call = new WCIFR_Call();

	}

	/**
	 * Scripts and style sheets
	 *
	 * @return void
	 */
	public function enqueue() {

		$screen = get_current_screen();

		if ( 'woocommerce_page_wc-importer-for-reviso' === $screen->id ) {
			wp_enqueue_script( 'chosen', WCIFR_URI . '/vendor/harvesthq/chosen/chosen.jquery.min.js' );
			wp_enqueue_script( 'tzcheckbox', WCIFR_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ) );

			wp_enqueue_style( 'chosen-style', WCIFR_URI . '/vendor/harvesthq/chosen/chosen.min.css' );
			wp_enqueue_style( 'font-awesome', '//use.fontawesome.com/releases/v5.8.1/css/all.css' );
			wp_enqueue_style( 'tzcheckbox-style', WCIFR_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css' );
		}

	}


	/**
	 * Check if the current page is the plugin options page
	 *
	 * @return boolean
	 */
	public function is_wcifr_admin() {

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'woocommerce_page_wc-importer-for-reviso' === $screen->id ) {
			return true;
		}

	}


	/**
	 * Save the Agreement Grant Token in the db
	 *
	 * @return void
	 */
	public function save_agt() {

		if ( $this->is_wcifr_admin() && isset( $_GET['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );

			update_option( 'wcifr-agt', $token );
		}

	}


	/**
	 * Deletes the Agreement Grant Token from the db
	 *
	 * @return void
	 */
	public function disconnect_callback() {

		delete_option( 'wcifr-agt' );

		exit;

	}


	/**
	 * Display the status of the connection to Reviso
	 *
	 * @param bool $return if true the method returns only if the connection is set.
	 * @return mixed
	 */
	public function check_connection_callback( $return = false ) {

		$response = $this->wcifr_call->call( 'get', 'self' );

		if ( isset( $response->httpStatusCode ) && isset( $response->message ) ) {

			echo false;

		} elseif ( isset( $response->application->appNumber ) ) {

			if ( $return ) {

				return true;

			} else {

				echo '<h4 class="wcifr-connection-status"><span class="label label-success">' . esc_html( __( 'Connected', 'wc-importer-for-reviso' ) ) . '</span></h4>';

			}
		}

		exit;

	}

}
new WCIFR_Settings( true );
