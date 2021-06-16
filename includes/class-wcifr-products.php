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

       	$this->temporary_data = new WCIFR_Temporary_Data( false, 'products' );
		$this->wcifr_call     = new WCIFR_Call();
        $this->post_status    = 1 === intval( get_option( 'wcifr-publish-new-products' ) ) ? 'publish' : 'draft';
        $this->product_sku    = get_option( 'wcifr-product-sku' );

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

        error_log( 'COUNT: ' . count( $output->collection ) );

		return $output;

	}


	/**
	 * Get WC tax class details
	 *
	 * @param  string $tax_rate_class set for a specific tax rate class.
	 * @return object
	 */
	private function get_wc_tax_class( $tax_rate_class = 'all' ) {

		global $wpdb;

        $tax_rate_class = 'standard' === $tax_rate_class ? null : $tax_rate_class;
		$where          = 'all' !== $tax_rate_class ? " WHERE tax_rate_class = '$tax_rate_class'" : '';

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'woocommerce_tax_rates' . $where;

		$results = $wpdb->get_results( $query );

		if ( $results && isset( $results[0] ) ) {
			return $results[0];
		}

	}


   /**
    * Get the standard tax rate
    *
    * @return int
    */ 
    private function get_standard_rate() {

        $result = $this->get_wc_tax_class( 'standard' );

        if ( isset( $result->tax_rate ) ) {

            return intval( $result->tax_rate );
            
        } else {
    
            return 99;

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

        $data  = json_decode( $product_data );
        error_log( 'DATA: ' . print_r( $data, true ) );

        $name           = isset( $data->name ) ? $data->name : null;
        $description    = isset( $data->description ) ? $data->description : $name;
        $product_number = isset( $data->productNumber ) ? $data->productNumber : null;
        $price          = isset( $data->recommendedPrice ) ? wc_format_decimal( $data->recommendedPrice, 2 ) : 0;
        $sell_price     = isset( $data->salesPrice ) ? wc_format_decimal( $data->salesPrice, 2 ) : 0;

        /* Sku */
        $sku = $product_number;

        if ( isset( $data->barCode ) && $this->product_sku ) {

            $sku = $data->barCode;

        }

        /* Stock */
        $available_qty  = null;
        $stock_status   = null;
        $manage_stock   = 'no';
        $total_sales    = null;

        if ( isset( $data->inventory ) ) {

            /* error_log( 'INVENTORY: ' . print_r( $data->inventory, true ) ); */

            $available_qty = isset( $data->inventory->available ) ? $data->inventory->available : 0;
            $stock_status  = 0 < $available_qty ? 'instock' : 'outofstock';
            $manage_stock  = 'yes';
            $total_sales   = isset( $data->inventory->orderedByCustomers ) ? $data->inventory->orderedByCustomers : null;

        }

        $args = array(
            'post_title'   => $name,
            'post_content' => $description,
            'post_type'    => 'product',
            'post_status'  => $this->post_status,

        );

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

            /* update_post_meta( $post_id, xxx ); */
            /* update_post_meta( $post_id, xxx ); */

            if ( $sell_price ) {

                update_post_meta( $post_id, '_price', $sell_price );
                update_post_meta( $post_id, '_sell_price', $sell_price );
                update_post_meta( $post_id, '_sale_price', $sell_price );

            } else {

                update_post_meta( $post_id, '_price', $price );

            }

        }

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

                if ( isset( $element['name'], $element['value'] ) )

                $output[ $element['name'] ] = $element['value'];

            }

        }

        return $output;

    }


    /**
     * Save the product import options
     *
     * @param array @data serialized form data values.
     *
     * @return void
     */
    public function save_options( $data = null ) {

        if ( $data && is_array( $data ) ) {

            $options = $this->prepare_array( $data );

            $wcifr_publish_new_products = isset( $options['wcifr-publish-new-products'] ) ? sanitize_text_field( $options['wcifr-publish-new-products'] ) : 0;
            update_option( 'wcifr-publish-new-products', $wcifr_publish_new_products );

            $wcifr_product_sku = isset( $options['wcifr-product-sku'] ) ? sanitize_text_field( $options['wcifr-product-sku'] ) : 0;
            update_option( 'wcifr-product-sku', $wcifr_product_sku );

        } else {

            update_option( 'wcifr-publish-new-products', 0 );
            update_option( 'wcifr-product-sku', 0 );

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

                    if ( 2 === $n ) {

                        $hash = md5( json_encode( $data ) );

                        /* Add temporary data to the db table */
                        $this->temporary_data->add_data( $hash, json_encode( $data ) );
                        
                        /*Schedule single event*/
                        as_enqueue_async_action(
                            'wcifr_import_single_product_event',
                            array(
                                'hash' => $hash,
                            ),
                            'wcifr_import_single_product'
                        );

                    }

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

		}

		exit;

	}

}
new WCIFR_Products( true );

