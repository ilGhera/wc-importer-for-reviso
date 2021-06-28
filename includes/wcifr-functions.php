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
 * Go Premium button
 *
 * @return void
 */
function wcifr_go_premium() {

	$title       = __( 'This is a premium functionality, click here for more information', 'wc-importer-for-reviso' );
	$output      = '<span class="wcifr label label-warning premium">';
		$output .= '<a href="https://www.ilghera.com/product/woocommerce-importer-for-reviso-premium" target="_blank" title="' . esc_attr( $title ) . '">Premium</a>';
	$output     .= '</span>';

	$allowed = array(
		'span' => array(
			'class' => array(),
		),
		'a'    => array(
			'target' => array(),
			'title'  => array(),
			'href'   => array(),
		),
	);

	echo wp_kses( $output, $allowed );

}

