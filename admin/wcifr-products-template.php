<?php
/**
 * Products options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

/* Get data */
$wcifr_publish_new_products   = get_option( 'wcifr-publish-new-products' );
$wcifr_product_sku            = get_option( 'wcifr-product-sku' );
$wcifr_short_description      = get_option( 'wcifr-short-description' );
$wcifr_exclude_title          = get_option( 'wcifr-exclude-title' );
$wcifr_exclude_description    = get_option( 'wcifr-exclude-description' );
$wcifr_products_not_available = get_option( 'wcifr-products-not-available');

?>

<!-- Import form -->
<form name="wcifr-import-products" class="wcifr-form products"  method="post" action="">

	<table class="form-table">
		<tr>
            <th scope="row"><?php esc_html_e( 'Product sku', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-product-sku" name="wcifr-product-sku" value="1"<?php echo 1 == $wcifr_product_sku ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php echo wp_kses_post( __( 'Use <i>Bar Code instead</i> of <i>Product Number</i> if available', 'wc-importer-for-reviso' ) ); ?></p>
			</td>
		</tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Exclude product title', 'wc-importer-for-reviso' ); ?></th>
            <td>
                <input type="hidden" name="wcifr-exclude-title" value="0">
                <input type="checkbox" name="wcifr-exclude-title" value="1"<?php echo $wcifr_exclude_title == 1 ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Exclude title from products updates', 'wc-importer-for-reviso' ); ?></p>
            </td>
        </tr>
		<tr>
            <th scope="row"><?php esc_html_e( 'Short description', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-short-description" name="wcifr-short-description" value="1"<?php echo 1 == $wcifr_short_description ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Use the excerpt as short product description', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Exclude product description', 'wc-importer-for-reviso' ); ?></th>
            <td>
                <input type="hidden" name="wcifr-exclude-description" value="0">
                <input type="checkbox" name="wcifr-exclude-description" value="1"<?php echo $wcifr_exclude_description == 1 ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Exclude descriptions from products updates', 'wc-importer-for-reviso' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Products not available', 'wc-importer-for-reviso' ); ?></th>
            <td>
                <input type="hidden" name="wcifr-products-not-available" value="0">
                <input type="checkbox" name="wcifr-products-not-available" value="1"<?php echo $wcifr_products_not_available == 1 ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Avoid creating new products if not available in stock', 'wc-importer-for-reviso' ); ?></p>
            </td>
        </tr>
		<tr>
            <th scope="row"><?php esc_html_e( 'Publish new products', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-publish-new-products" name="wcifr-publish-new-products" value="1"<?php echo 1 == $wcifr_publish_new_products ? ' checked="checked"' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Publish new products directly', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
	</table>

	<input type="submit" name="wcifr-products-import" class="button-primary wcifr import products" value="<?php esc_html_e( 'Import from Reviso', 'wc-importer-for-reviso' ); ?>">

</form>

<?php
/*Nonce*/
$import_products_nonce = wp_create_nonce( 'wcifr-import-products' );

wp_localize_script(
	'wcifr-js',
	'wcifrProducts',
	array(
		'importNonce' => $import_products_nonce,
	)
);

