<?php
/**
 * Customers options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

?>

<!-- Export form -->
<form name="wcifr-export-customers" class="wcifr-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php echo esc_html_e( 'User role', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<select class="wcifr-customers-role wcifr-select" name="wcifr-customers-role">
					<?php
					global $wp_roles;
					$roles = $wp_roles->get_names();

					/*Get value from the db*/
					$customers_role = get_option( 'wcifr-customers-role' );

					foreach ( $roles as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . ( $key === $customers_role ? ' selected="selected"' : '' ) . '> ' . esc_html( __( $value, 'woocommerce' ) ) . '</option>';
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select your customers user role', 'wc-importer-for-reviso' ); ?></p>

			</td>
		</tr>
	</table>

	<p class="submit">
		<input type="submit" name="download_csv" class="button-primary wcifr import-users customers" value="<?php esc_html_e( 'Import from Reviso', 'wc-importer-for-reviso' ); ?>" />
	</p>

</form>

