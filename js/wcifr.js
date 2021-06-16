/**
 * JS
 * 
 * @author ilGhera
 * @package wc-importer-for-reviso/js
 * @since 0.9.0
 */

var wcifrController = function() {

	var self = this;

	self.onLoad = function() {
	    self.wcifr_pagination();
		self.tzCheckbox();
	    self.wcifr_import_users();
	    self.wcifr_delete_remote_users();
		// self.get_user_groups('customers');
		self.get_user_groups('suppliers');
		self.wcifr_import_products();
		self.wcifr_import_orders();
		self.wcifr_delete_remote_products();
		self.wcifr_delete_remote_orders();
		self.wcifr_disconnect();
		self.book_invoice();
		self.wcifr_check_connection();
	}


	/**
	 * Delete the admin messages
	 */
	self.delete_messages = function() {

		jQuery(function($){

			$('.yes, .not', '.wcifr-message ').html('');

		})

	}


	/**
	 * Tab navigation
	 */
	self.wcifr_pagination = function() {

		jQuery(function($){

			var contents = $('.wcifr-admin')
			var url = window.location.href.split("#")[0];
			var hash = window.location.href.split("#")[1];

			if(hash) {
		        contents.hide();		    
			    $('#' + hash).fadeIn(200);		
		        $('h2#wcifr-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
		        $('h2#wcifr-admin-menu a').each(function(){
		        	if($(this).data('link') == hash) {
		        		$(this).addClass('nav-tab-active');
		        	}
		        })
		        
		        $('html, body').animate({
		        	scrollTop: 0
		        }, 'slow');
			}

			$("h2#wcifr-admin-menu a").click(function () {
		        var $this = $(this);
		        
		        contents.hide();
		        $("#" + $this.data("link")).fadeIn(200);

		        self.chosen(true);
		        self.chosen();

		        $('h2#wcifr-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
		        $this.addClass('nav-tab-active');

		        window.location = url + '#' + $this.data('link');

		        $('html, body').scrollTop(0);

		        /*Delete the admin messages*/
		        self.delete_messages();

		    })

		})
	        	
	}


	/**
	 * Checkboxes
	 */
	self.tzCheckbox = function() {

		jQuery(function($){
			$('input[type=checkbox]').tzCheckbox({labels:['On','Off']});
		});

	}


	/**
	 * Plugin tools available only if connected to Reviso
	 */
	self.wcifr_tools_control = function(deactivate = false) {

		jQuery(function($){

			if(deactivate) {

				$('.wcifr-form').addClass('disconnected');
				$('.wcifr-form.connection').removeClass('disconnected');

				$('.wcifr-form input').attr('disabled','disabled');
				$('.wcifr-form select').attr('disabled','disabled');

				$('.wcifr-suppliers-groups, .wcifr-customers-groups').addClass('wcifr-select');
		        self.chosen(true);

			} else {

				$('.wcifr-form').removeClass('disconnected');
				$('.wcifr-form input').removeAttr('disabled');
				$('.wcifr-form select').removeAttr('disabled');

			}


		})

	}
		

	/**
	 * Check the connection to Reviso
	 */
	self.wcifr_check_connection = function() {

		jQuery(function($){

			var data = {
				'action': 'wcifr-check-connection'
			}

			$.post(ajaxurl, data, function(response){

				if(response) {

					/*Activate plugin tools*/
					self.wcifr_tools_control();
			
					$('.check-connection').html(response);
					$('.wcifr-connect').hide();
					$('.wcifr-disconnect').css('display', 'inline-block');
					$('.wcifr-disconnect').animate({
						opacity: 1
					}, 500);

				} else {

					/*Deactivate plugin tools*/
					self.wcifr_tools_control(true);

				}

			})

		})

	}


	/**
	 * Disconnect from Reviso deleting the Agreement Grant Tocken from the db
	 */
	self.wcifr_disconnect = function() {

		jQuery(function($){

			$(document).on('click', '.wcifr-disconnect', function(){

				var data = {
					'action': 'wcifr-disconnect'
				}

				$.post(ajaxurl, data, function(response){
					location.reload();
				})

			})

		})

	}


	/**
	 * Adds a spinning gif to the message box waiting for the response
	 */
	self.wcifr_response_loading = function() {

		jQuery(function($){

			var container = $('.wcifr-message .yes');

			$(container).html('<div class="wcifr-loading"><img></div>');
			$('img', container).attr('src', wcifrSettings.responseLoading);

		})

	}


	/**
	 * Scroll page to the response message
	 */
	self.wcifr_response_scroll = function() {

		jQuery(function($){
	        
	        $('html, body').animate({
	        	scrollTop: $('.wcifr-message').offset().top
	        }, 'slow');

		})

	}


	/**
	 * Show a message to the admin
	 * @param  {string} message the text
	 * @param  {bool}   error   different style with true
	 */
	self.wcifr_response_message = function(message, error = false, update = false) {

		jQuery(function($){

			/*Remove the loading gif*/
			$('.wcifr-message .yes').html('');

			var container	  = error ? $('.wcifr-message .not') : $('.wcifr-message .yes');
			var message_class = error ? 'alert-danger' : 'alert-info';
			var icon		  = error ? 'fa-exclamation-triangle' : 'fa-info-circle';
			
			if ( update ) {

				$(container).append( '<div class="bootstrap-iso"><div class="alert ' + message_class + '"><b><i class="fas ' + icon + '"></i>WC Importer for Reviso </b> - ' + message + '</div>' );

			} else {

				$(container).html( '<div class="bootstrap-iso"><div class="alert ' + message_class + '"><b><i class="fas ' + icon + '"></i>WC Importer for Reviso </b> - ' + message + '</div>' );

			}

		})

	}


	/**
	 * Export WP users to Reviso
	 */
	self.wcifr_import_users = function() {

		jQuery(function($){

			$('.button-primary.wcifr.import-users').on('click', function(e){

				e.preventDefault();

				self.delete_messages();
				self.wcifr_response_loading();
				self.wcifr_response_scroll();

				var type   = $(this).hasClass('customers') ? 'customers' : 'suppliers';
				var role   = $('.wcifr-' + type + '-role').val();
				var groups = $('.wcifr-' + type + '-groups').val();

				var data = {
					'action': 'wcifr-import-users',
					'wcifr-import-users-nonce': wcifrUsers.importNonce,
					'type': type,
					'role': role,
					'groups': groups
				}

				$.post(ajaxurl, data, function(response){

					if (response) {

						var result = JSON.parse(response);

						for (var i = 0; i < result.length; i++) {

							var error = 'error' === result[i][0] ? true : false;
							var update = 0 !== i ? true : false; 

							self.wcifr_response_message( result[i][1], error, false );

						}

					}

				})
			
			})

		})

	}


	/**
	 * Delete all the users from Reviso
	 */
	self.wcifr_delete_remote_users = function() {

		jQuery(function($){

			$('.button-primary.wcifr.red.users').on('click', function(e){

				e.preventDefault();

				self.delete_messages();

				var type   = $(this).hasClass('customers') ? 'customers' : 'suppliers';
				var answer = confirm( 'Vuoi cancellare tutti i ' + type + ' da Reviso?' ); //temp.

				if ( answer ) {

					self.wcifr_response_loading();
					self.wcifr_response_scroll();

					var data = {
						'action': 'wcifr-delete-remote-users',
						'wcifr-delete-users-nonce': wcifrUsers.deleteNonce,
						'type': type
					}


					$.post(ajaxurl, data, function(response){

						if (response) {

							var result = JSON.parse(response);

							for (var i = 0; i < result.length; i++) {

								var error = 'error' === result[i][0] ? true : false;
								var update = 0 !== i ? true : false; 

								self.wcifr_response_message( result[i][1], error, false );
		
							}

						}

					})

				}

			})

		})

	}


	/**
	 * Export products to Reviso
	 */
	self.wcifr_import_products = function() {

		jQuery(function($){

			$('.button-primary.wcifr.import.products').on('click', function(e){

				e.preventDefault();

				self.delete_messages();
				self.wcifr_response_loading();
				self.wcifr_response_scroll();

                var formData = $('.wcifr-form.products').serializeArray();

				var data = {
					'action': 'wcifr-import-products',
					'wcifr-import-products-nonce': wcifrProducts.importNonce,
					'options': formData
				}

				$.post(ajaxurl, data, function(response){

					if (response) {

						var result = JSON.parse(response);

						for (var i = 0; i < result.length; i++) {

							var error = 'error' === result[i][0] ? true : false;
							var update = 0 !== i ? true : false; 

							self.wcifr_response_message( result[i][1], error, false );

						}

					}

				})

			})

		})

	}


	/**
	 * Delete all the products from Reviso
	 */
	self.wcifr_delete_remote_products = function() {

		jQuery(function($){

			$('.button-primary.wcifr.red.products').on('click', function(e){

				e.preventDefault();

				self.delete_messages();
							
				var answer = confirm( 'Vuoi cancellare tutti i prodotti da Reviso?' );

				if ( answer ) {

					self.wcifr_response_loading();
					self.wcifr_response_scroll();

					var data = {
						'action': 'wcifr-delete-remote-products',
						'wcifr-delete-products-nonce': wcifrProducts.deleteNonce,
					}

					$.post(ajaxurl, data, function(response){


						if (response) {

							var result = JSON.parse(response);

							for (var i = 0; i < result.length; i++) {

								var error = 'error' === result[i][0] ? true : false;
								var update = 0 !== i ? true : false; 

								self.wcifr_response_message( result[i][1], error, false );
		
							}

						}

					})

				}

			})

		})

	}


	/**
	 * Show customers and suppliers groups in the plugin options page
	 * @param {string} type customer or supplier
	 */
	self.get_user_groups = function(type) {

		jQuery(function($){

            var optionSelected;
            var isTabOrders;
            var selectClass;
            var selected;
			var groups;

			var data = {
				'action': 'wcifr-get-' + type + '-groups',
				'confirm': 'yes' 
			}

			$.post(ajaxurl, data, function(response){

				if (response) {

					groups = JSON.parse(response);

                    $('.wcifr-' + type + '-groups').each(function(){

                        isTabOrders    = $(this).hasClass('wcifr-orders-customers-group') ? true : false;
                        selectClass    = isTabOrders ? 'wcifr-select-large' : 'wcifr-select';
                        optionSelected = JSON.parse( wcifrUsers.selectedSuppliersGroups ); 

                        if (typeof groups === 'object') {

                            for (key in groups) {        

                                selected = optionSelected.indexOf(key) > -1 ? ' selected="selected"' : false;
                                $(this).append('<option value="' + key + '"' + selected + '>' + groups[key] + '</option>');

                            }

                        } else {

                            $(this).append('<option>' + groups + '</option>');

                        }

                        $(this).addClass(selectClass);
                        self.chosen(true);

                    })

				}

			})

		})

	}


	/**
	 * Export orders to Reviso
	 */
	self.wcifr_import_orders = function() {

		jQuery(function($){

			$('.button-primary.wcifr.import.orders').on('click', function(e){

				e.preventDefault();

				self.delete_messages();
				self.wcifr_response_loading();
				self.wcifr_response_scroll();

				var statuses = $('.wcifr-orders-statuses').val();

				var data = {
					'action': 'wcifr-import-orders',
					'wcifr-import-orders-nonce': wcifrOrders.importNonce,
					'statuses': statuses
				}

				$.post(ajaxurl, data, function(response){

					if (response) {

						var result = JSON.parse(response);

						for (var i = 0; i < result.length; i++) {

							var error = 'error' === result[i][0] ? true : false;
							var update = 0 !== i ? true : false; 

							self.wcifr_response_message( result[i][1], error, false );

						}

					}

				})

			})

		})

	}


	/**
	 * Delete all orders from Reviso
	 */
	self.wcifr_delete_remote_orders = function() {

		jQuery(function($){

			$('.button-primary.wcifr.red.orders').on('click', function(e){

				e.preventDefault();

				self.delete_messages();
								
				var answer = confirm( 'Vuoi cancellare tutti gli ordini da Reviso?' );

				if ( answer ) {

					self.wcifr_response_loading();
					self.wcifr_response_scroll();

					var data = {
						'action': 'wcifr-delete-remote-orders',
						'wcifr-delete-orders-nonce': wcifrOrders.deleteNonce,
					}

					$.post(ajaxurl, data, function(response){

						if (response) {

							var result = JSON.parse(response);

							for (var i = 0; i < result.length; i++) {

								var error = 'error' === result[i][0] ? true : false;
								var update = 0 !== i ? true : false; 

								self.wcifr_response_message( result[i][1], error, false );

							}

						}

					})

				}

			})

		})

	}


	/**
	 * Show the book invoices option only with issue invoices option activated
	 */
	self.book_invoice = function() {

		jQuery(function($){

			var create_invoice_button = $('.wcifr-create-invoices-field td span.tzCheckBox');
			var issue_invoice_button  = $('.wcifr-issue-invoices-field td span.tzCheckBox');
			var	invoices_field        = $('.wcifr-invoices-field');
			var	book_invoices_field   = $('.wcifr-book-invoices-field');
			var	send_invoices_field   = $('.wcifr-send-invoices-field');
			
			if ( $(create_invoice_button).hasClass('checked') ) {
				
				invoices_field.show();

				if ( $(issue_invoice_button).hasClass('checked') ) {

					book_invoices_field.show();
					send_invoices_field.show();

				} else {
				
					book_invoices_field.hide();			
					send_invoices_field.hide();			
		
				}

			}

			/*Hide all if invoices creation is disabled*/
			$(create_invoice_button).on( 'click', function(){

				if ( $(this).hasClass('checked') ) {
				
					invoices_field.show();

					if ( $(issue_invoice_button).hasClass('checked') ) {

					book_invoices_field.show();
					send_invoices_field.show();

				} else {
				
					book_invoices_field.hide();			
					send_invoices_field.hide();			
		
				}
				
				} else {
				
					invoices_field.hide('slow');
	
				}

			})

			/*Hide options related to the issue of invoices*/
			$(issue_invoice_button).on( 'click', function(){
				
				if ( $(this).hasClass('checked') ) {
				
					book_invoices_field.show();
					send_invoices_field.show();
				
				} else {
				
					book_invoices_field.hide('slow');			
					send_invoices_field.hide('slow');			
		
				}

			})
		})

	}


	/**
	 * Fires Chosen
	 * @param  {bool} destroy method distroy
	 */
	self.chosen = function(destroy = false) {

		jQuery(function($){

			$('.wcifr-select').chosen({
		
				disable_search_threshold: 10,
				width: '200px'
			
			});

			$('.wcifr-select-large').chosen({
		
				disable_search_threshold: 10,
				width: '290px'
			
			});

		})

	}


}


/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wcifrController;
	Controller.onLoad();

});
