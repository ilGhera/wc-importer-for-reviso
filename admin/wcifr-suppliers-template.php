<?php
/**
 * Suppliers options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

?>

<!-- Export form -->
<form name="wcifr-import-suppliers" class="wcifr-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'User role', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<select class="wcifr-suppliers-role wcifr-select" name="wcifr-suppliers-role">
					<?php
					global $wp_roles;
					$roles = $wp_roles->get_names();

					/*Get the value from the db*/
					$suppliers_role = get_option( 'wcifr-suppliers-role' );

					foreach ( $roles as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . ( $key === $suppliers_role ? ' selected="selected"' : '' ) . '> ' . esc_html( __( $value, 'woocommerce' ) ) . '</option>';
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select your suppliers user role', 'wc-importer-for-reviso' ); ?></p>

			</td>
		</tr>
	</table>

	<p class="submit">
		<input type="submit" name="download_csv" class="button-primary wcifr import-users suppliers" value="<?php esc_html_e( 'Import from Reviso', 'wc-importer-for-reviso' ); ?>" />
	</p>

</form>

<?php
/*Nonce*/
$import_users_nonce = wp_create_nonce( 'wcifr-import-users' );

wp_localize_script(
	'wcifr-js',
	'wcifrUsers',
	array(
		'importNonce'             => $import_users_nonce,
		'selectedSuppliersGroups' => wp_json_encode( get_option( 'wcifr-suppliers-groups' ) ),
	)
);

