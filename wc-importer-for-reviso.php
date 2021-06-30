<?php
/**
 * Plugin Name: WC Importer for Reviso
 * Plugin URI: https://www.ilghera.com/product/woocommerce-importer-for-reviso-premium
 * Description: Connect your store to Reviso and import orders, products, customers and suppliers.
 * Author: ilGhera
 * Version: 0.9.0
 *
 * Author URI: https://ilghera.com
 * Requires at least: 4.0
 * Tested up to: 5.7
 * WC tested up to: 5
 * Text Domain: wc-importer-for-reviso
 */

/**
 * Handles the plugin activation
 *
 * @return void
 */
function load_wc_importer_for_reviso() {

	/*Function check */
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	/*Internationalization*/
	load_plugin_textdomain( 'wc-importer-for-reviso', false, basename( dirname( __FILE__ ) ) . '/languages' );

	/*Constants declaration*/
	define( 'WCIFR_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WCIFR_URI', plugin_dir_url( __FILE__ ) );
	define( 'WCIFR_FILE', __FILE__ );
	define( 'WCIFR_ADMIN', WCIFR_DIR . 'admin/' );
	define( 'WCIFR_DIR_NAME', basename( dirname( __FILE__ ) ) );
	define( 'WCIFR_INCLUDES', WCIFR_DIR . 'includes/' );
	define( 'WCIFR_CLASSES', WCIFR_DIR . 'classes/' );
	define( 'WCIFR_SETTINGS', admin_url( 'admin.php?page=wc-importer-for-reviso' ) );

	/*Files required*/
	require_once WCIFR_DIR . 'libraries/action-scheduler/action-scheduler.php';
	require_once WCIFR_ADMIN . 'class-wcifr-admin.php';
	require_once WCIFR_CLASSES . 'class-wcifr-temporary-data.php';
	require_once WCIFR_INCLUDES . 'wcifr-functions.php';
	require_once WCIFR_INCLUDES . 'class-wcifr-call.php';
	require_once WCIFR_INCLUDES . 'class-wcifr-settings.php';
	require_once WCIFR_INCLUDES . 'class-wcifr-users.php';

}
add_action( 'after_setup_theme', 'load_wc_importer_for_reviso', 1 );

