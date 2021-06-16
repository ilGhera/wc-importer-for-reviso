<?php
/**
 * Products options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

/* Get data */
$wcifr_publish_new_products = get_option( 'wcifr-publish-new-products' );
$wcifr_product_sku          = get_option( 'wcifr-product-sku' );
?>

<!-- Import form -->
<form name="wcifr-import-products" class="wcifr-form products"  method="post" action="">

	<table class="form-table">
		<tr>
            <th scope="row"><?php esc_html_e( 'Publish new products', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-publish-new-products" name="wcifr-publish-new-products" value="1"<?php echo 1 == $wcifr_publish_new_products ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Publish new products directly', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr>
            <th scope="row"><?php esc_html_e( 'Product sku', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-product-sku" name="wcifr-product-sku" value="1"<?php echo 1 == $wcifr_product_sku ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php echo wp_kses_post( __( 'Use <i>Bar Code instead</i> of <i>Product Number</i> if available', 'wc-importer-for-reviso' ) ); ?></p>
			</td>
		</tr>
	</table>

	<input type="submit" name="wcifr-products-import" class="button-primary wcifr import products" value="<?php esc_html_e( 'Import from Reviso', 'wc-importer-for-reviso' ); ?>">

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
$import_products_nonce = wp_create_nonce( 'wcifr-import-products' );
$delete_products_nonce = wp_create_nonce( 'wcifr-delete-products' );

wp_localize_script(
	'wcifr-js',
	'wcifrProducts',
	array(
		'importNonce' => $import_products_nonce,
		'deleteNonce' => $delete_products_nonce,
	)
);
