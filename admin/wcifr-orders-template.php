<?php
/**
 * Orders options
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

?>

<!-- Export form -->
<form name="wcifr-orders" class="wcifr-form wcifr-orders-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Order status', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<select class="wcifr-orders-statuses wcifr-select" name="wcifr-orders-statuses[]" multiple data-placeholder="<?php esc_html_e( 'All orders types', 'wc-importer-for-reviso' ); ?>">
					<?php
					$saved_statuses = get_option( 'wcifr-orders-statuses' ) ? get_option( 'wcifr-orders-statuses' ) : array();
					$statuses = wc_get_order_statuses();
					foreach ( $statuses as $key => $value ) {
						echo '<option name="' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '"';
						echo ( in_array( $key, $saved_statuses ) ) ? ' selected="selected">' : '>';
						echo esc_html( __( $value, 'wc-importer-for-reviso' ) ) . '</option>';
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select which orders to export', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
	</table>

	<input type="submit" class="button-primary wcifr export orders" value="<?php esc_html_e( 'Export to Reviso', 'wc-importer-for-reviso' ); ?>">

</form>


<!-- Delete form -->
<form name="wcifr-delete-orders" id="wcifr-delete-orders" class="wcifr-form one-of"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Delete orders', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<p class="description"><?php esc_html_e( 'Delete all orders on Reviso', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" class="button-primary wcifr red orders" value="<?php esc_html_e( 'Delete from Reviso', 'wc-importer-for-reviso' ); ?>" />
	</p>

</form>

<?php
/*Nonce*/
$export_orders_nonce = wp_create_nonce( 'wcifr-export-orders' );
$delete_orders_nonce = wp_create_nonce( 'wcifr-delete-orders' );

wp_localize_script(
	'wcifr-js',
	'wcifrOrders',
	array(
		'exportNonce' => $export_orders_nonce,
		'deleteNonce' => $delete_orders_nonce,
	)
);
?>

<!-- Settings form -->
<form name="wcifr-orders-settings" class="wcifr-form"  method="post" action="">

	<h2><?php esc_html_e( 'Orders settings', 'wc-importer-for-reviso' ); ?></h2>

	<?php
	$wcifr_export_orders          = get_option( 'wcifr-export-orders' );
	$wcifr_create_invoices        = get_option( 'wcifr-create-invoices' );
	$wcifr_issue_invoices         = get_option( 'wcifr-issue-invoices' );
	$wcifr_send_invoices          = get_option( 'wcifr-send-invoices' );
	$wcifr_book_invoices          = get_option( 'wcifr-book-invoices' );
	$wcifr_number_series          = get_option( 'wcifr-number-series-prefix' );
	$wcifr_number_series_receipts = get_option( 'wcifr-number-series-receipts-prefix' );
	$wcifr_orders_customers_group = get_option( 'wcifr-orders-customers-group' );

	if ( isset( $_POST['wcifr-orders-settings-sent'], $_POST['wcifr-orders-settings-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcifr-orders-settings-nonce'] ), 'wcifr-orders-settings' ) ) {

		$wcifr_export_orders = isset( $_POST['wcifr-export-orders'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-export-orders'] ) ) : 0;
		update_option( 'wcifr-export-orders', $wcifr_export_orders );

		$wcifr_create_invoices = isset( $_POST['wcifr-create-invoices'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-create-invoices'] ) ) : 0;
		update_option( 'wcifr-create-invoices', $wcifr_create_invoices );

		$wcifr_issue_invoices = isset( $_POST['wcifr-issue-invoices'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-issue-invoices'] ) ) : 0;
		update_option( 'wcifr-issue-invoices', $wcifr_issue_invoices );

		$wcifr_send_invoices = isset( $_POST['wcifr-send-invoices'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-send-invoices'] ) ) : 0;
		update_option( 'wcifr-send-invoices', $wcifr_send_invoices );

		$wcifr_book_invoices = isset( $_POST['wcifr-book-invoices'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-book-invoices'] ) ) : 0;
		update_option( 'wcifr-book-invoices', $wcifr_book_invoices );

		$wcifr_number_series = isset( $_POST['wcifr-number-series'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-number-series'] ) ) : $wcifr_number_series;
		update_option( 'wcifr-number-series-prefix', $wcifr_number_series );
    
        $wcifr_number_series_receipts = isset( $_POST['wcifr-number-series-receipts'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-number-series-receipts'] ) ) : $wcifr_number_series_receipts;
		update_option( 'wcifr-number-series-receipts-prefix', $wcifr_number_series_receipts );

        $wcifr_orders_customers_group = isset( $_POST['wcifr-orders-customers-group'] ) ? sanitize_text_field( wp_unslash( $_POST['wcifr-orders-customers-group'] ) ) : $wcifr_orders_customers_group;
		update_option( 'wcifr-orders-customers-group', $wcifr_orders_customers_group );
	}
	?>

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Export orders', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" name="wcifr-export-orders" value="1"<?php echo 1 == $wcifr_export_orders ? ' checked="checked"' : ''; ?>>
				<p class="description"><?php esc_html_e( 'Export orders to Reviso automatically', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr class="wcifr-create-invoices-field">
			<th scope="row"><?php esc_html_e( 'Create invoices', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" name="wcifr-create-invoices" value="1"<?php echo 1 == $wcifr_create_invoices ? ' checked="checked"' : ''; ?>>
				<p class="description"><?php esc_html_e( 'Create invoices in Reviso for completed orders', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr class="wcifr-invoices-field wcifr-issue-invoices-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Issue invoices', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-issue-invoices" name="wcifr-issue-invoices" value="1"<?php echo 1 == $wcifr_issue_invoices ? ' checked="checked"' : ''; ?>>
				<p class="description"><?php esc_html_e( 'Issue invoices created in Reviso directly ', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr class="wcifr-invoices-field wcifr-send-invoices-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Send invoices', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" class="wcifr-send-invoices" name="wcifr-send-invoices" value="1"<?php echo 1 == $wcifr_send_invoices ? ' checked="checked"' : ''; ?>>
				<p class="description"><?php esc_html_e( 'Attach invoices to completed order notifications', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr class="wcifr-invoices-field wcifr-book-invoices-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Book invoices', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<input type="checkbox" name="wcifr-book-invoices" value="1"<?php echo 1 == $wcifr_book_invoices ? ' checked="checked"' : ''; ?>>
				<p class="description"><?php esc_html_e( 'Book invoices created in Reviso directly ', 'wc-importer-for-reviso' ); ?></p>
			</td>
		</tr>
		<tr class="wcifr-number-series-field">
			<th scope="row"><?php esc_html_e( 'Number series', 'wc-importer-for-reviso' ); ?></th>
            <td>
                <div class="wcifr-number-series">
                    <?php
                    /* $class = new WCIFR_Orders(); */
                    /* $get_remote_series = $class->get_remote_number_series( null, 'customerInvoice' ); */

                    if ( isset( $get_remote_series ) && is_array( $get_remote_series ) && ! empty( $get_remote_series ) ) {

                        echo '<select name="wcifr-number-series" class="wcifr-select-large">';

                        foreach ( $get_remote_series as $single ) {

                            $checked = $single->prefix === $wcifr_number_series ? ' selected="selected"' : '';

                            echo '<option value="' . esc_attr( $single->prefix ) . '"' . esc_attr( $checked ) . '>' . esc_html( $single->prefix ) . ' - ' . esc_html( $single->name ) . '</option>';

                        }

                        echo '</select>';

                    }
                    ?>
                    <p class="description"><?php echo wp_kses_post( __( 'Choose the series of numbers to use for <b>Invoices</b>', 'wc-importer-for-reviso' ) ); ?></p>
                </div>
                <div class="wcifr-number-series-receipts">
                    <?php
                    if ( isset( $get_remote_series ) && is_array( $get_remote_series ) && ! empty( $get_remote_series ) ) {

                        echo '<select name="wcifr-number-series-receipts" class="wcifr-select-large">';

                        foreach ( $get_remote_series as $single ) {

                            $checked = $single->prefix === $wcifr_number_series_receipts ? ' selected="selected"' : '';

                            echo '<option value="' . esc_attr( $single->prefix ) . '"' . esc_attr( $checked ) . '>' . esc_html( $single->prefix ) . ' - ' . esc_html( $single->name ) . '</option>';

                        }

                        echo '</select>';

                    }
                    ?>
                    <p class="description"><?php echo wp_kses_post( __( 'Choose the series of numbers to use for <b>Receipts</b>', 'wc-importer-for-reviso' ) ); ?></p>
                </div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Customers group', 'wc-importer-for-reviso' ); ?></th>
			<td>
            <select data-group-selected="<?php echo esc_attr( $wcifr_orders_customers_group ); ?>" class="wcifr-customers-groups wcifr-orders-customers-group" name="wcifr-orders-customers-group">
                    <option value="0"><?php esc_html_e( 'Auto', 'wc-importer-for-reviso' ); ?></option>
                </select>
				<p class="description"><?php echo wp_kses_post( __( 'Select a specific group of Reviso customers or use <i>Auto</i> for national and foreign groups', 'wc-importer-for-reviso' ) ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wcifr-orders-settings', 'wcifr-orders-settings-nonce' ); ?>
	
	<p class="submit">
		<input type="submit" name="wcifr-orders-settings-sent" class="button-primary wcifr orders-settings" value="<?php esc_html_e( 'Save options', 'wc-importer-for-reviso' ); ?>" />
	</p>

</form>
