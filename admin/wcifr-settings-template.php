<?php
/**
 * General settings
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/admin
 * @since 0.9.0
 */

?>

<!-- Reviso connection -->
<form name="wcifr-settings" class="wcifr-form connection one-of"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Connection status', 'wc-importer-for-reviso' ); ?></th>
			<td>
				<div class="bootstrap-iso">
					<div class="check-connection">
						<h4 class="wcifr-connection-status"><span class="wcifr label label-danger"><?php esc_html_e( 'Not connected', 'wc-importer-for-reviso' ); ?></span></h4>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Connect with your Reviso credentials', 'wc-importer-for-reviso' ); ?></p>				
			</td>
		</tr>
	</table>

	<a class="button-primary wcifr-connect" href="https://app.reviso.com/api1/requestaccess.aspx?appPublicToken=iRxYo7PUDBHSsw6Kd63uLRM86FDx1O0HERqbknB2hhg1&locale=it-IT&redirectUrl=<?php echo esc_url( WCIFR_SETTINGS ); ?>"><?php esc_html_e( 'Connect to Reviso', 'wc-importer-for-reviso' ); ?></a>
	<a class="button-primary wcifr-disconnect red"><?php esc_html_e( 'Disconnect from Reviso', 'wc-importer-for-reviso' ); ?></a>

</form>

<?php
/*Pass data to the script file*/
wp_localize_script(
	'wcifr-js',
	'wcifrSettings',
	array(
		'responseLoading' => WCIFR_URI . 'images/loader.gif',
	)
);
