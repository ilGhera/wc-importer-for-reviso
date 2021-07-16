<?php
/**
 * Functions
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/includes
 * @since 0.9.0
 */

/**
 * Returns the string passed less long than the limit specified
 *
 * @param  string $text  the full text.
 * @param  int    $limit the string length limit.
 * @return string
 */
function wcifr_avoid_length_exceed( $text, $limit ) {

    $output = $text;

    if ( strlen( $text ) > $limit ) {

        if ( 25 === intval( $limit ) ) {

            /*Product number (sku)*/
            $output = substr( $text, 0, $limit );

        } else {

            /*Product name and description*/
            $output = substr( $text, 0, ( $limit - 4 ) ) . ' ...';

        }

    }

    return $output;

}


/**
 * Sanitize every single array element
 *
 * @param  array $array the array to sanitize.
 * @return array        the sanitized array.
 */
function wcifr_sanitize_array( $array ) {

    $output = array();

    if ( is_array( $array ) && ! empty( $array ) ) {

        foreach ( $array as $key => $value ) {

            $output[ $key ] = sanitize_text_field( wp_unslash( $value ) );

        }

    }

    return $output;

}


/**
 * Update checker
 */
require( WCIFR_DIR . 'plugin-update-checker/plugin-update-checker.php' );

$wcifr_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.ilghera.com/wp-update-server-2/?action=get_metadata&slug=wc-importer-for-reviso-premium',
	WCIFR_FILE,
	'wc-importer-for-reviso-premium'
);


/**
 * Secure update check with the Premium Key
 *
 * @param  array $query_args the default args.
 * @return array            the updated args
 */
function wcifr_secure_update_check( $query_args ) {

	$key = base64_encode( get_option( 'wcifr-premium-key' ) );

	if ( $key ) {

		$query_args['premium-key'] = $key;

	}

	return $query_args;

}
$wcifr_update_checker->addQueryArgFilter( 'wcifr_secure_update_check' );


/**
 * Plugin update message
 *
 * @param  array $plugin_data plugin information.
 * @param  array $response    available plugin update information.
 */
function wcifr_update_message( $plugin_data, $response ) {

	$key = get_option( 'wcifr-premium-key' );

	$message = null;

	if ( ! $key ) {

		$message = 'A <b>Premium Key</b> is required for keeping this plugin up to date. Please, add yours in the <a href="' . WCIFR_SETTINGS . '">options page</a> or click <a href="https://www.ilghera.com/product/woocommerce-importer-for-reviso-premium/" target="_blank">here</a> for prices and details.';

	} else {

		$decoded_key = explode( '|', base64_decode( $key ) );
		$bought_date = date( 'd-m-Y', strtotime( $decoded_key[1] ) );
		$limit = strtotime( $bought_date . ' + 365 day' );
		$now = strtotime( 'today' );

		if ( $limit < $now ) {

			$message = 'It seems like your <strong>Premium Key</strong> is expired. Please, click <a href="https://www.ilghera.com/product/woocommerce-importer-for-reviso-premium/" target="_blank">here</a> for prices and details.';

		} elseif ( '9055' !== $decoded_key[2] ) {

			$message = 'It seems like your <strong>Premium Key</strong> is not valid. Please, click <a href="https://www.ilghera.com/product/woocommerce-importer-for-reviso-premium/" target="_blank">here</a> for prices and details.';

		}

	}

	$allowed_tags = array(
		'strong' => array(),
		'a'      => array(
			'href'   => array(),
			'target' => array(),
		),
	);

	echo ( $message ) ? '<br><span class="wcifr-alert">' . wp_kses( $message, $allowed_tags ) . '</span>' : '';

}
add_action( 'in_plugin_update_message-' . WCIFR_DIR_NAME . '/wc-importer-for-reviso.php', 'wcifr_update_message', 10, 2 );
