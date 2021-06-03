<?php
/**
 * Checkout template file
 *
 * @author ilGhera
 * @package wc-checkout-fields/templates
 * @since 1.0.2
 */

$wcefr_company_invoice = get_option( 'wcefr_company_invoice' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_company_invoice = isset( $_POST['wcefr_company_invoice'] ) ? $_POST['wcefr_company_invoice'] : 0;
    update_option( 'wcefr_company_invoice', $wcefr_company_invoice );
    update_option( 'billing_wcefr_piva_active', $wcefr_company_invoice );
}

$wcefr_private_invoice = get_option( 'wcefr_private_invoice' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_private_invoice = isset( $_POST['wcefr_private_invoice'] ) ? $_POST['wcefr_private_invoice'] : 0;
    update_option( 'wcefr_private_invoice', $wcefr_private_invoice );
}

$wcefr_private = get_option( 'wcefr_private' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_private = isset( $_POST['wcefr_private'] ) ? $_POST['wcefr_private'] : 0;
    update_option( 'wcefr_private', $wcefr_private );
}

/*Aggiorno cf nel db in base alle opzioni precedenti*/
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    if ( $wcefr_company_invoice === 0 && $wcefr_private_invoice === 0 && $wcefr_private === 0 ) {
        update_option( 'billing_wcefr_cf_active', 0 );
    } else {
        update_option( 'billing_wcefr_cf_active', 1 );
    }
}

$wcefr_document_type = get_option( 'wcefr_document_type' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_document_type = isset( $_POST['wcefr_document_type'] ) ? $_POST['wcefr_document_type'] : 0;
    update_option( 'wcefr_document_type', $wcefr_document_type );
}

$wcefr_cf_mandatory = get_option( 'wcefr_cf_mandatory' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_cf_mandatory = isset( $_POST['wcefr_cf_mandatory'] ) ? $_POST['wcefr_cf_mandatory'] : 0;
    update_option( 'wcefr_cf_mandatory', $wcefr_cf_mandatory );
}

$wcefr_fields_check = get_option( 'wcefr_fields_check' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_fields_check = isset( $_POST['wcefr_fields_check'] ) ? $_POST['wcefr_fields_check'] : 0;
    update_option( 'wcefr_fields_check', $wcefr_fields_check );
}

$wcefr_vies_check = get_option( 'wcefr_vies_check' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_vies_check = isset( $_POST['wcefr_vies_check'] ) ? $_POST['wcefr_vies_check'] : 0;
    update_option( 'wcefr_vies_check', $wcefr_vies_check );
}

$wcefr_pec_active = get_option( 'billing_wcefr_pec_active' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_pec_active = isset( $_POST['wcefr_pec_active'] ) ? $_POST['wcefr_pec_active'] : 0;
    update_option( 'billing_wcefr_pec_active', $wcefr_pec_active );
}

$wcefr_pa_code_active = get_option( 'billing_wcefr_pa_code_active' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_pa_code_active = isset( $_POST['wcefr_pa_code_active'] ) ? $_POST['wcefr_pa_code_active'] : 0;
    update_option( 'billing_wcefr_pa_code_active', $wcefr_pa_code_active );
}

$wcefr_piva_only_ue = get_option( 'wcefr_piva_only_ue' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_piva_only_ue = isset( $_POST['wcefr_piva_only_ue'] ) ? $_POST['wcefr_piva_only_ue'] : 0;
    update_option( 'wcefr_piva_only_ue', $wcefr_piva_only_ue );
}

$wcefr_only_italy = get_option( 'wcefr_only_italy' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_only_italy = isset( $_POST['wcefr_only_italy'] ) ? $_POST['wcefr_only_italy'] : 0;
    update_option( 'wcefr_only_italy', $wcefr_only_italy );
}

$wcefr_cf_only_italy = get_option( 'wcefr_cf_only_italy' );
if ( isset( $_POST['wcefr-options-sent'] ) ) {
    $wcefr_cf_only_italy = isset( $_POST['wcefr_cf_only_italy'] ) ? $_POST['wcefr_cf_only_italy'] : 0;
    update_option( 'wcefr_cf_only_italy', $wcefr_cf_only_italy );
}
?>


<h3 class="wcefr"><?php esc_html_e( 'Checkout page', 'wc-exporter-for-reviso' ); ?></h3>

<!--Form Fornitori-->
<form name="wcefr-options-submit" id="wcefr-options-submit"  method="post" action="">
    <table class="form-table">
        <tr>
			<th scope="row"><?php esc_html_e( 'Tax documents', 'wc-exporter-for-reviso' ); ?></th>
            <td>
                <p style="margin-bottom: 10px;">
                    <label for="wcefr_company_invoice">
                        <input type="checkbox" name="wcefr_company_invoice" value="1"<?php echo $wcefr_company_invoice == 1 ? ' checked="checked"' : ''; ?>>
						<?php echo '<span class="tax-document">' . esc_html( __( 'Company ( Invoice )', 'wc-exporter-for-reviso' ) ) . '</span>'; ?>
                    </label>							
                </p>
                <p style="margin-bottom: 10px;">
                    <label for="wcefr_private_invoice">
                        <input type="checkbox" name="wcefr_private_invoice" value="1"<?php echo $wcefr_private_invoice == 1 ? ' checked="checked"' : ''; ?>>
						<?php echo '<span class="tax-document">' . esc_html( __( 'Private ( Invoice )', 'wc-exporter-for-reviso' ) ) . '</span>'; ?>
                    </label>
                </p>
                <p>
                    <label for="wcefr_private">
                        <input type="checkbox" name="wcefr_private" value="1"<?php echo $wcefr_private == 1 ? ' checked="checked"' : ''; ?>>
						<?php echo '<span class="tax-document">' . esc_html( __( 'Private ( Receipt )', 'wc-exporter-for-reviso' ) ) . '</span>'; ?>
                    </label>
                </p>
				<p class="description"><?php esc_html_e( 'By activating one or more types of invoice, the fields VAT and Tax Code will be displayed when needed.', 'wc-exporter-for-reviso' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( __( 'Document type', 'wc-exporter-for-reviso' ) ); ?></th>
            <td>
                <label for="wcefr_document_type">
                    <input type="checkbox" name="wcefr_document_type" value="1"<?php echo $wcefr_document_type == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Put the document type selector on the top of the form', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
			<th scope="row"><?php esc_html_e( 'Tax Code required', 'wc-exporter-for-reviso' ); ?></th>
            <td>
                <label for="wcefr_cf_mandatory">
                    <select class="wcefr" name="wcefr_cf_mandatory">
                        <option value="0"<?php echo 0 == $wcefr_cf_mandatory ? ' selected="selected"' : null;  ?>><?php esc_html_e( 'Never', 'wc-exporter-for-reviso' ); ?></option>
                        <option value="1"<?php echo 1 == $wcefr_cf_mandatory ? ' selected="selected"' : null;  ?>><?php esc_html_e( 'Only with receipts', 'wc-exporter-for-reviso' ); ?></option>
                        <option value="2"<?php echo 2 == $wcefr_cf_mandatory ? ' selected="selected"' : null;  ?>><?php esc_html_e( 'Only with invoices', 'wc-exporter-for-reviso' ); ?></option>
                        <option value="3"<?php echo 3 == $wcefr_cf_mandatory ? ' selected="selected"' : null;  ?>><?php esc_html_e( 'Always', 'wc-exporter-for-reviso' ); ?></option>
                    </select>
                </label>
				<p class="description"><?php esc_html_e( 'Make the Tax Code field mandatory', 'wc-exporter-for-reviso' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo __( 'Tax Code validation', 'wc-exporter-for-reviso' ); ?></th>
            <td>
                <label for="wcefr_fields_check">
                    <input type="checkbox" name="wcefr_fields_check" value="1"<?php echo $wcefr_fields_check == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Activate the Tax Code validity check', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( __( 'VIES validation', 'wc-exporter-for-reviso' ) ); ?></th>
            <td>
                <label for="wcefr_vies_check">
                    <input type="checkbox" name="wcefr_vies_check" value="1"<?php echo $wcefr_vies_check == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php echo wp_kses_post( __( 'Activate the VIES validity check for the VAT Number <i>( SOAP activated on server is required )</i>', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo __( 'PEC', 'wc-exporter-for-reviso' ); ?></th>
            <td>
                <label for="wcefr_pec_active">
                    <input type="checkbox" name="wcefr_pec_active" value="1"<?php echo $wcefr_pec_active == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Activate the PEC field for eletronic invoicing', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( __( 'Recipient code', 'wc-exporter-for-reviso' ) ); ?></th>
            <td>
                <label for="wcefr-pa-code">
                    <input type="checkbox" name="wcefr_pa_code_active" value="1"<?php echo $wcefr_pa_code_active == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Activate the Recipient Code field for electronic invoicing', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( __( 'Only EU', 'wc-exporter-for-reviso' ) ); ?></th>
            <td>
                <label for="wcefr_piva_only_ue">
                    <input type="checkbox" name="wcefr_piva_only_ue" value="1"<?php echo $wcefr_piva_only_ue == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Make VAT Number field mandatory only for European Union countries ', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( __( 'Only Italy', 'wc-exporter-for-reviso' ) ); ?></th>
            <td>
                <label for="wcefr_only_italy">
                    <input type="checkbox" name="wcefr_only_italy" value="1"<?php echo $wcefr_only_italy == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Show PEC and Recipient Code fields only to Italian customers', 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <label for="wcefr_cf_only_italy">
                    <input type="checkbox" name="wcefr_cf_only_italy" value="1"<?php echo $wcefr_cf_only_italy == 1 ? ' checked="checked"' : ''; ?>>
                </label>
                <p class="description"><?php esc_html_e( __( 'Show the tax code field only to Italian customers'
                , 'wc-exporter-for-reviso' ) ); ?></p>
            </td>
        </tr>


    </table>
    <?php wp_nonce_field( 'wcefr-options-submit', 'wcefr-options-nonce' ); ?>
    <p class="submit">
        <input type="submit" name="wcefr-options-sent" class="button-primary" value="<?php esc_attr_e( 'Save settings', 'wc-exporter-for-reviso' ); ?>" />
    </p>
</form>
