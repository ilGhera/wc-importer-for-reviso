/**
 * JS - Shop Orders
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/js
 * @since 0.9.0
 */

jQuery(document).ready(function($) {

	$('.wcifr-pdf-download').each(function(){
		
		$(this).on('click', function(){

			var order_id = $(this).data('order-id');

			var data = {
				'action': 'wcifr-download-pdf',
				'order-id': order_id
			}

			$.post(ajaxurl, data, function(response){

				window.open('http://localhost/wp-dev/wp-content/plugins/wc-importer-for-reviso/includes/wcifr-invoice-preview.php?preview=true', 'Invoice preview', 'width=800, height=600');
			
			})

		})
	
	})

})
