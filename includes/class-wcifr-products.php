<?php
/**
 * Import products from Reviso
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/includes
 * @since 0.9.0
 */
class WCIFR_Products {

	/**
	 * Class constructor
	 *
	 * @param boolean $init fire hooks if true.
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'wp_ajax_wcifr-import-products', array( $this, 'import_products' ) );
			add_action( 'wcifr_import_single_product_event', array( $this, 'import_single_product' ) );

		}

		$this->temporary_data         = new WCIFR_Temporary_Data( false, 'products' );
		$this->wcifr_call             = new WCIFR_Call();
		$this->post_status            = 1 === intval( get_option( 'wcifr-publish-new-products' ) ) ? 'publish' : 'draft';
		$this->product_sku            = get_option( 'wcifr-product-sku' );
		$this->wc_prices_include_tax  = 'yes' === get_option( 'woocommerce_prices_include_tax' ) ? true : false;
		$this->short_description      = get_option( 'wcifr-short-description' );
		$this->exclude_title          = get_option( 'wcifr-exclude-title' );
		$this->exclude_description    = get_option( 'wcifr-exclude-description' );
		$this->products_not_available = get_option( 'wcifr-products-not-available' );

	}

	/**
	 * Check if the inventory module is active
	 *
	 * @return bool
	 */
	private function inventory_module() {

		$output   = false;
		$response = $this->wcifr_call->call( 'get', 'self' );

		if ( is_array( $response ) && isset( $response['modules'] ) ) {

			if ( is_array( $response['modules'] ) ) {

				foreach ( $response['modules'] as $module ) {

					if ( 'Lager' === $module->name ) {

						$output = true;

						continue;

					}
				}
			}
		}

		return $output;

	}


	/**
	 * Generate the short product description
	 *
	 * @param  string $description the full product description.
	 *
	 * @return string
	 */
	private function wcifr_get_short_description( $description ) {

		$output      = null;
		$description = wp_strip_all_tags( $description );

		if ( strlen( $description ) > 340 ) {

			$output = substr( $description, 0, 340 ) . '...';

		} else {

			$output = $description;

		}

		return $output;

	}


	/**
	 * Get all the products from Reviso
	 *
	 * @return array
	 */
	private function get_remote_products() {

		$output = $this->wcifr_call->call( 'get', 'products?pagesize=1000' );

		$results = isset( $output->pagination->results ) ? $output->pagination->results : '';

		if ( 1000 < $results ) {

			$limit = $results / 1000;

			for ( $i = 1; $i < $limit; $i++ ) {

				$get_products = $this->wcifr_call->call( 'get', 'products?skippages=' . $i . '&pagesize=1000' );

				if ( isset( $get_products->collection ) && ! empty( $get_products->collection ) ) {

					$output->collection = array_merge( $output->collection, $get_products->collection );

				} else {

					continue;

				}
			}
		}

		return $output;

	}


	/**
	 * Turn a multidimensional array into a sinple one
	 *
	 * @param array $array the multidimensional array.
	 *
	 * @return array
	 */
	private function prepare_array( $array ) {

		$output = array();

		if ( is_array( $array ) ) {

			foreach ( $array as $element ) {

				if ( isset( $element['name'], $element['value'] ) ) {

					$output[ $element['name'] ] = $element['value'];

				}
			}
		}

		return $output;

	}


	/**
	 * Save the product import options
	 *
	 * @param array $data serialized form data values.
	 *
	 * @return void
	 */
	private function save_options( $data = null ) {

		if ( $data && is_array( $data ) ) {

			$options = $this->prepare_array( $data );

			$wcifr_publish_new_products = isset( $options['wcifr-publish-new-products'] ) ? sanitize_text_field( $options['wcifr-publish-new-products'] ) : 0;
			update_option( 'wcifr-publish-new-products', $wcifr_publish_new_products );

			$wcifr_product_sku = isset( $options['wcifr-product-sku'] ) ? sanitize_text_field( $options['wcifr-product-sku'] ) : 0;
			update_option( 'wcifr-product-sku', $wcifr_product_sku );

			$wcifr_short_description = isset( $options['wcifr-short-description'] ) ? sanitize_text_field( $options['wcifr-short-description'] ) : 0;
			update_option( 'wcifr-short-description', $wcifr_short_description );

			$wcifr_exclude_title = isset( $options['wcifr-exclude-title'] ) ? sanitize_text_field( $options['wcifr-exclude-title'] ) : 0;
			update_option( 'wcifr-exclude-title', $wcifr_exclude_title );

			$wcifr_esclude_description = isset( $options['wcifr-exclude-description'] ) ? sanitize_text_field( $options['wcifr-exclude-description'] ) : 0;
			update_option( 'wcifr-exclude-description', $wcifr_esclude_description );

			$wcifr_products_not_available = isset( $options['wcifr-products-not-available'] ) ? sanitize_text_field( $options['wcifr-products-not-available'] ) : 0;
			update_option( 'wcifr-products-not-available', $wcifr_products_not_available );

		} else {

			update_option( 'wcifr-publish-new-products', 0 );
			update_option( 'wcifr-product-sku', 0 );
			update_option( 'wcifr-short-description', 0 );
			update_option( 'wcifr-exclude-title', 0 );
			update_option( 'wcifr-exclude-description', 0 );
			update_option( 'wcifr-products-not-available', 0 );

		}

	}


	/**
	 * Get the product id by sku
	 *
	 * @param string $sku the product sku.
	 *
	 * return int the prduct id
	 */
	private function get_product_id_by_sku( $sku ) {

		global $wpdb;

		$product_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sku' AND meta_value = %s",
				$sku
			)
		);

		return $product_id;

	}


	/**
	 * Create a new WooCommerce Tax Class
	 *
	 * @param object $vat_account    the product vat account.
	 * @param string $tax_rate_class the tax rate class name.
	 *
	 * @return bool
	 */
	private function add_tax_rate( $vat_account, $tax_rate_class = '' ) {

		$store_location = wc_get_base_location();
		$country        = isset( $store_location['country'] ) ? $store_location['country'] : 'IT'; // Temp.

		global $wpdb;

		$response = $wpdb->insert(
			$wpdb->prefix . 'woocommerce_tax_rates',
			array(
				'tax_rate_country'  => $country,
				'tax_rate'          => number_format( $vat_account->ratePercentage, 4 ),
				'tax_rate_name'     => $vat_account->vatCode,
				'tax_rate_priority' => 1,
				'tax_rate_shipping' => 0,
				'tax_rate_class'    => $tax_rate_class,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
			)
		);

		if ( ! is_wp_error( $response ) ) {

			return true;

		}

	}


	/**
	 * Create a new WooCommerce Tax Class
	 *
	 * @param object $vat_account the product vat account.
	 *
	 * @return string the tax rate class name
	 */
	private function add_tax_class( $vat_account ) {

		/* Don't add standard rate tax class */
		$class_name = 19 < intval( $vat_account->ratePercentage ) ? '' : $vat_account->vatCode;

		if ( $class_name ) {

			$tax_classes   = explode( "\n", get_option( 'woocommerce_tax_classes' ) );
			$tax_classes[] = $class_name;

			update_option( 'woocommerce_tax_classes', implode( "\n", $tax_classes ) );

			global $wpdb;

			$wpdb->insert(
				$wpdb->prefix . 'wc_tax_rate_classes',
				array(
					'name' => $class_name,
					'slug' => sanitize_title_with_dashes( $class_name ),
				),
				array(
					'%s',
					'%s',
				)
			);

		}

		if ( $this->add_tax_rate( $vat_account, $class_name ) ) {

			return $class_name;

		}

	}


	/**
	 * Get the first sales account number of product group
	 *
	 * @param int $group_id the product group id.
	 *
	 * $return int the sales account number
	 */
	private function get_sales_account_number( $group_id ) {

		$response = $this->wcifr_call->call( 'get', 'product-groups/' . $group_id );

		if ( isset( $response->salesAccountsList ) && is_array( $response->salesAccountsList ) ) {

			if ( isset( $response->salesAccountsList[0]->salesAccount->accountNumber ) ) {

				return $response->salesAccountsList[0]->salesAccount->accountNumber;

			}
		}

	}


	/**
	 * Get the first sales account of product group
	 *
	 * @param int $group_id the product group id.
	 *
	 * $return int the sales account number
	 */
	private function get_sales_account( $group_id ) {

		$sales_account_number = $this->get_sales_account_number( $group_id );
		$output               = $this->wcifr_call->call( 'get', 'accounts/' . $sales_account_number );

		return $output;

	}


	/**
	 * Get WC tax class by percentage rate
	 *
	 * @param object $vat_account the product vat account.
	 *
	 * @return string the tax class name
	 */
	private function get_wc_tax_class( $vat_account ) {

		global $wpdb;

		$output        = null;
		$tax_rate_name = 19 < intval( $vat_account->ratePercentage ) ? '' : $vat_account->vatCode;
		$tax_rate      = number_format( $vat_account->ratePercentage, 4 );
		$and           = $tax_rate_name ? ' AND tax_rate_name = %s' : null;
		$values        = $and ? array( $tax_rate, $tax_rate_name ) : $tax_rate;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'woocommerce_tax_rates WHERE tax_rate = %f' . $and,
				$values
			)
		);

		if ( ! $result ) {

			$output = $this->add_tax_class( $vat_account );

		} else {

			$output = isset( $result->tax_rate_name ) ? $result->tax_rate_name : null;

		}

		return $output;

	}


	/**
	 * Get the Reviso vat account applayed to the product
	 *
	 * @param int $group_id the product group id.
	 *
	 * $return object the vat account
	 */
	private function get_vat_account( $group_id ) {

		$sales_account = $this->get_sales_account( $group_id );

		if ( isset( $sales_account->vatAccount->vatCode ) ) {

			$response = $this->wcifr_call->call( 'get', 'vat-accounts/' . $sales_account->vatAccount->vatCode );

			if ( ! is_wp_error( $response ) ) {

				return $response;

			}
		}

	}


	/**
	 * Prepare the single product data for Reviso
	 *
	 * @param  string $product_data the Reviso product data json encoded.
	 *
	 * @return array
	 */
	private function prepare_product_data( $product_data ) {

		$data           = json_decode( $product_data );
		$name           = isset( $data->name ) ? $data->name : null;
		$description    = isset( $data->description ) ? $data->description : $name;
		$product_number = isset( $data->productNumber ) ? $data->productNumber : null;
		$get_price      = isset( $data->recommendedPrice ) ? wc_format_decimal( $data->recommendedPrice, 2 ) : 0;
		$get_sell_price = isset( $data->salesPrice ) ? wc_format_decimal( $data->salesPrice, 2 ) : 0;

		/* Tax */
		if ( isset( $data->productGroup->productGroupNumber ) ) {

			$vat_account    = $this->get_vat_account( $data->productGroup->productGroupNumber );
			$vat_percentage = 0;

			if ( is_object( $vat_account ) ) {

				$vat_percentage = isset( $vat_account->ratePercentage ) ? $vat_account->ratePercentage : 0;
				$tax_class      = $this->get_wc_tax_class( $vat_account );

			}
		}

		/* Prices incliding tax */
		if ( $this->wc_prices_include_tax ) {

			$price      = $get_price + wc_format_decimal( $get_price * ( $vat_percentage / 100 ), 2 );
			$sell_price = $get_sell_price + wc_format_decimal( $get_sell_price * ( $vat_percentage / 100 ), 2 );

		} else {

			$price      = $get_price;
			$sell_price = $get_sell_price;

		}

		/* Sku */
		$sku = $product_number;

		if ( isset( $data->barCode ) && $this->product_sku ) {

			$sku = $data->barCode;

		}

		/* Stock */
		$available_qty = null;
		$stock_status  = null;
		$manage_stock  = 'no';
		$total_sales   = null;

		if ( isset( $data->inventory ) ) {

			$available_qty = isset( $data->inventory->available ) ? $data->inventory->available : 0;
			$stock_status  = 0 < $available_qty ? 'instock' : 'outofstock';
			$manage_stock  = 'yes';
			$total_sales   = isset( $data->inventory->orderedByCustomers ) ? $data->inventory->orderedByCustomers : null;

		}

		$args = array(
			'post_type'   => 'product',
			'post_status' => $this->post_status,

		);

		/* Product ID */
		$id = $this->get_product_id_by_sku( $sku );

		if ( $id ) {

			$args['ID'] = $id;

			/* Update options */
			if ( $this->exclude_title ) {

				$name = get_the_title( $id );

			}

			if ( $this->exclude_description ) {

				$description = get_post_field( 'post_content', $id );

			}
		} else {

			if ( $this->products_not_available && 'outofstock' === $stock_status ) {

				return;

			}
		}

		$args['post_title']   = $name;
		$args['post_content'] = $description;

		/* Short description */
		if ( $this->short_description ) {

			$args['post_excerpt'] = $this->wcifr_get_short_description( $description );

		}

		/* Insert product */
		$post_id = wp_insert_post( $args );

		if ( ! is_wp_error( $post_id ) ) {

			wp_set_object_terms( $post_id, 'simple', 'product_type' );

			update_post_meta( $post_id, '_sku', $sku );
			update_post_meta( $post_id, '_stock', $available_qty );
			update_post_meta( $post_id, '_stock_status', $stock_status );
			update_post_meta( $post_id, '_manage_stock', $manage_stock );
			update_post_meta( $post_id, '_visibility', 'visible' );
			update_post_meta( $post_id, '_regular_price', $price );
			update_post_meta( $post_id, 'total_sales', $total_sales );

			if ( $sell_price ) {

				update_post_meta( $post_id, '_price', $sell_price );
				update_post_meta( $post_id, '_sell_price', $sell_price );
				update_post_meta( $post_id, '_sale_price', $sell_price );

			} else {

				update_post_meta( $post_id, '_price', $price );

			}

			/* Tax */
			if ( isset( $data->productGroup->productGroupNumber ) ) {

				if ( 0 === intval( $vat_percentage ) ) {

					update_post_meta( $post_id, '_tax_status', 'none' );

				} else {

					update_post_meta( $post_id, '_tax_status', 'taxable' );
					update_post_meta( $post_id, '_tax_class', $tax_class );

				}
			}
		}

	}


	/**
	 * Import single product from Reviso
	 *
	 * @param  string $hash the hash code of the specific data.
	 *
	 * @return void
	 */
	public function import_single_product( $hash ) {

		$temp_data = $this->temporary_data->get_data( $hash );
		$data      = $this->prepare_product_data( $temp_data );

		/* Delete temporary data */
		$this->temporary_data->delete_data( $hash );

	}


	/**
	 * Import Reviso products in WooCommerce
	 *
	 * @return void
	 */
	public function import_products() {

		if ( isset( $_POST['wcifr-import-products-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcifr-import-products-nonce'] ), 'wcifr-import-products' ) ) {

			$options = isset( $_POST['options'] ) ? $_POST['options'] : null;

			/* Save options */
			$this->save_options( $options );

			/* Get products */
			$products = $this->get_remote_products();

			if ( isset( $products->collection ) && is_array( $products->collection ) ) {

				$n = 0;

				foreach ( $products->collection as $data ) {

					$n++;

					$hash = md5( wp_json_encode( $data ) );

					/* Add temporary data to the db table */
					$this->temporary_data->add_data( $hash, wp_json_encode( $data ) );

					/* Schedule single event */
					as_enqueue_async_action(
						'wcifr_import_single_product_event',
						array(
							'hash' => $hash,
						),
						'wcifr_import_single_product'
					);

				}

				$response[] = array(
					'ok',
					/* translators: the products count */
					esc_html( sprintf( __( '%d product(s) import process has begun', 'wc-importer-for-reviso' ), $n ) ),
				);

			} else {

				$response[] = array(
					'error',
					esc_html( __( 'ERROR! There are not products to import', 'wc-importer-for-reviso' ) ),
				);

			}

			echo wp_json_encode( $response );

		}

		exit;

	}

}
new WCIFR_Products( true );

