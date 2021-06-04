<?php
/**
 * Products options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

?>

<!-- Export form -->
<form name="wcifr-export-products" class="wcifr-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Products categories', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<select class="wcifr-products-categories wcifr-select" name="wcifr-products-categories[]" multiple data-placeholder="<?php esc_html_e( 'All categories', 'wc-importer-for-reviso' ); ?>">
					<?php
					$terms = get_terms( 'product_cat' );

					/*Get the value from the db*/
					$products_categories = get_option( 'wcifr-products-categories' );

					if ( $terms ) {
						foreach ( $terms as $single_term ) {

							$selected = is_array( $products_categories ) && in_array( $single_term->term_id, $products_categories ) ? ' selected="selected"' : '';

							echo '<option value="' . esc_attr( $single_term->term_id ) . '"' . esc_html( $selected ) . '>' . esc_html( $single_term->name ) . '</option>';
						}
					}
					?>

				</select>
				<p class="description"><?php esc_html_e( 'Select which categories to send to Reviso', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
	</table>

	<input type="submit" name="wcifr-products-export" class="button-primary wcifr export products" value="<?php esc_html_e( 'Export to Reviso', 'wc-importer-for-reviso' ); ?>">

</form>


<!-- Delete form -->
<form name="wcifr-delete-products" id="wcifr-delete-products" class="wcifr-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Delete products', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<p class="description"><?php esc_html_e( 'Delete all products on Reviso. Note that you cannot delete a product that has been used on an Invoice.', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" class="button-primary wcifr red products" value="<?php esc_html_e( 'Delete from Reviso', 'wc-importer-for-reviso' ); ?>" />
	</p>

</form>

<?php
/*Nonce*/
$export_products_nonce = wp_create_nonce( 'wcifr-export-products' );
$delete_products_nonce = wp_create_nonce( 'wcifr-delete-products' );

wp_localize_script(
	'wcifr-js',
	'wcifrProducts',
	array(
		'exportNonce' => $export_products_nonce,
		'deleteNonce' => $delete_products_nonce,
	)
);
