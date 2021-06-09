<?php
/**
 * Export customer and suppliers to Reviso
 *
 * @author ilGhera
 * @package wc-importer-for-reviso/includes
 * @since 0.9.0
 */
class WCIFR_Users {

	/**
	 * Class constructor
	 *
	 * @param boolean $init fire hooks if true.
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			/* add_action( 'wp_ajax_wcifr-import-users', array( $this, 'import_users' ) ); */
			add_action( 'wp_ajax_wcifr-import-users', array( $this, 'get_remote_users' ) );
			add_action( 'wp_ajax_wcifr-get-customers-groups', array( $this, 'get_customers_groups' ) );
			add_action( 'wp_ajax_wcifr-get-suppliers-groups', array( $this, 'get_suppliers_groups' ) );
			add_action( 'wcifr_import_single_user_event', array( $this, 'import_single_user' ), 10, 3 );

		}

		$this->wcifr_call = new WCIFR_Call();

	}


	/**
	 * Get the customers/ suppliers groups from Reviso
	 *
	 * @param  string $type customer or supplier.
	 * @return array
	 */
	public function get_user_groups( $type ) {

		$output = array();

		/*From plural to singular as required by the endpoint*/
		$endpoint = substr( $type, 0, -1 );

		$groups = $this->wcifr_call->call( 'get', $endpoint . '-groups' );

		$field_name = 'customers' === $type ? 'customerGroupNumber' : 'supplierGroupNumber';

		if ( isset( $groups->collection ) ) {

			foreach ( $groups->collection as $group ) {

				$output[ $group->$field_name ] = $group->name;

			}

		}

		return $output;

	}


	/**
	 * Callback - Get suppliers groups
	 */
	public function get_suppliers_groups() {

		$output = $this->get_user_groups( 'suppliers' );
		echo json_encode( $output );

		exit;

	}


	/**
	 * Callback - Get customers groups
	 */
	public function get_customers_groups() {

		$output = $this->get_user_groups( 'customers' );
		echo json_encode( $output );

		exit;

	}


	/**
	 * Get customers and suppliers from Reviso
	 *
	 * @param string $type the type of user.
	 * @param int    $customer_number the specific customer to get.
	 * @return array
	 */
	public function get_remote_users() {

		if ( isset( $_POST['wcifr-import-users-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcifr-import-users-nonce'] ), 'wcifr-import-users' ) ) {

			$type   = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$role   = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
			$groups = isset( $_POST['groups'] ) && is_array( $_POST['groups'] ) ? wcifr_sanitize_array( $_POST['groups'] ) : '';

            /* error_log( 'POST: ' . print_r( $_POST, true ) ); */
            error_log( 'GROUPS: ' . print_r( $groups, true ) );

			/*Salvo le impostazioni nel database*/
			update_option( 'wcifr-' . $type . '-role', $role );
			update_option( 'wcifr-' . $type . '-groups', $groups );

            $filter  = $groups ? '?filter=supplierGroup$in:[' . implode( ',', $groups ) . ']' : null;

            error_log( 'FILTER: ' . $filter );

            $results = $this->wcifr_call->call( 'get', $type, $filter );

            if ( isset( $results->collection ) ) {

                $count = count( $results->collection );

                error_log( 'SUPPLIERS: ' . print_r( $results->collection, true) );

                /* return $results->collection; */

                $message_type = substr( $type, 0, -1 );
                $response[] = array(
                    'ok',
                    /* translators: 1: users count 2: user type */
                    esc_html( sprintf( __( '%1$d %2$s(s) import process has begun', 'wc-importer-for-reviso' ), $count, $message_type ) ),
                );

                echo json_encode( $response );

            }

        }

        exit;

	}


	/**
	 * Prepare the single user data to import to Reviso
	 *
	 * @param  int    $user_id the WP user id.
	 * @param  string $type  customers or suppliers.
	 * @param  object $order the WC order to get the customer details.
	 * @return array
	 */
	public function prepare_user_data( $user_id, $type, $order = null ) {

		$type_singular = substr( $type, 0, -1 );

		if ( $user_id ) {

			$user_details = get_userdata( $user_id );

			$user_data = array_map(
				function( $a ) {
					return $a[0];
				},
				get_user_meta( $user_id )
			);

			$name                    = isset( $user_data['billing_first_name'], $user_data['billing_last_name'] ) ? $user_data['billing_first_name'] . ' ' . $user_data['billing_last_name'] : '';
			$user_email              = isset( $user_data['billing_email'] ) ? $user_data['billing_email'] : '';
			$country                 = isset( $user_data['billing_country'] ) ? $user_data['billing_country'] : '';
			$city                    = isset( $user_data['billing_city'] ) ? $user_data['billing_city'] : '';
			$state                   = isset( $user_data['billing_state'] ) ? $user_data['billing_state'] : '';
			$address                 = isset( $user_data['billing_address_1'] ) ? $user_data['billing_address_1'] : '';
			$postcode                = isset( $user_data['billing_postcode'] ) ? $user_data['billing_postcode'] : '';
			$phone                   = isset( $user_data['billing_phone'] ) ? $user_data['billing_phone'] : '';
			$website                 = $user_details->user_url;
			$vat_number              = isset( $user_data['billing_wcifr_piva'] ) ? $user_data['billing_wcifr_piva'] : null;
			$identification_number   = isset( $user_data['billing_wcifr_cf'] ) ? $user_data['billing_wcifr_cf'] : null;
			$italian_certified_email = isset( $user_data['billing_wcifr_pec'] ) ? $user_data['billing_wcifr_pec'] : null;
			$public_entry_number     = isset( $user_data['billing_wcifr_pa_code'] ) ? $user_data['billing_wcifr_pa_code'] : null;

		} elseif ( $order ) {

			$name                    = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			$user_email              = $order->get_billing_email();
			$country                 = $order->get_billing_country();
			$city                    = $order->get_billing_city();
			$state                   = $order->get_billing_state();
			$address                 = $order->get_billing_address_1();
			$postcode                = $order->get_billing_postcode();
			$phone                   = $order->get_billing_phone();
			$vat_number              = $order->get_meta( '_billing_wcifr_piva' ) ? $order->get_meta( '_billing_wcifr_piva' ) : null;
			$identification_number   = $order->get_meta( '_billing_wcifr_cf' ) ? $order->get_meta( '_billing_wcifr_cf' ) : null;
			$italian_certified_email = $order->get_meta( '_billing_wcifr_pec' ) ? $order->get_meta( '_billing_wcifr_pec' ) : null;
			$public_entry_number     = $order->get_meta( '_billing_wcifr_pa_code' ) ? $order->get_meta( '_billing_wcifr_pa_code' ) : null;

		} else {

			return;

		}

        $base_location = wc_get_base_location();
        $shop_country  = is_array( $base_location ) && isset( $base_location['country'] ) ? $base_location['country'] : null;

		/*Reviso VatZone based on user country */
		$vat_zone = $shop_country === $country ? 1 : 3;

		/*Reviso's group selected by the admin*/
		if ( $order ) {
	
            $get_customers_groups = get_option( 'wcifr-orders-customers-group' );

            if ( 0 === intval( $get_customers_groups ) ) {

                /* By nationality */
                $group = $shop_country === $country ? 1 : 2;

            } else {

                /* Custom group */
                $group = $get_customers_groups;

            }

		} else {

			$group = get_option( 'wcifr-' . $type . '-group' );

		}

		$args = array(
			'name'                   => $name,
			'email'                  => $user_email,
			'currency'               => 'EUR', // temp.
			'country'                => $country,
			'city'                   => $city,
			'address'                => $address,
			'zip'                    => $postcode,
			'telephoneAndFaxNumber'  => $phone,
			'vatZone' => array(
				'vatZoneNumber' => $vat_zone,
			),
			'paymentTerms'           => array(
				'paymentTermsNumber' => 6,
			),
			'countryCode'            => array(
				'code' => $country,
			),
			$type_singular . 'Group' => array(
				$type_singular . 'GroupNumber' => intval( $group ),
			),
		);

		if ( 'IT' === $country ) {
			$args['province'] = array(
				'countryCode' => array(
					'code' => $country,
				),
				'ProvinceNumber' => $this->get_province_number( $state ),
			);
		}

		if ( isset( $website ) ) {
			$args['website'] = $website;
		}

		if ( $vat_number ) {
			$args['vatNumber'] = $vat_number;
		}

		if ( $identification_number ) {
			$args['corporateIdentificationNumber'] = $identification_number;
		}

		if ( $italian_certified_email ) {
			$args['italianCertifiedEmail'] = $italian_certified_email;
		}

		if ( $public_entry_number ) {
			$args['publicEntryNumber'] = $public_entry_number;
		}

		return $args;

	}


	/**
	 * Export single WP user to Reviso
	 *
	 * @param  int    $user_id the WP user.
	 * @param  string $type    customer or supplier.
	 * @param  object $order   the WC order to get the customer details.
	 * @return void
	 */
	public function import_single_user( $user_id, $type, $order = null ) {

		$args      = $this->prepare_user_data( $user_id, $type, $order );
		$remote_id = $this->user_exists( $type, $args['email'] );

		if ( $args ) {

			if ( ! $remote_id ) {

				$output = $this->wcifr_call->call( 'post', $type . '/', $args );

			} else {

				$output = $this->wcifr_call->call( 'put', $type . '/' . $remote_id, $args );

			}

			/*Log the error*/
			if ( ( isset( $output->errorCode ) || isset( $output->developerHint ) ) && isset( $output->message ) ) {

				error_log( 'WCIFR ERROR | User ID ' . $user_id . ' | ' . $output->message );

			} else {

				return $output;

			}

		}

	}


	/**
	 * Export WP users as customers/ suppliers in Reviso
	 *
	 * @return void
	 */
	public function import_users() {

		if ( isset( $_POST['wcifr-import-users-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcifr-import-users-nonce'] ), 'wcifr-import-users' ) ) {

			$type  = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$role  = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
			$group = isset( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '';

			/*Salvo le impostazioni nel database*/
			update_option( 'wcifr-' . $type . '-role', $role );
			update_option( 'wcifr-' . $type . '-group', $group );

			$args     = array( 'role' => $role );
			$users    = get_users( $args );
			$response = array();

			if ( $users ) {

				$n = 0;

				foreach ( $users as $user ) {

					$n++;

					/*Schedule single event*/
					as_enqueue_async_action(
						'wcifr_import_single_user_event',
						array(
							'user_id'   => $user->ID,
							'user_type' => $type,
						),
						'wcifr_import_single_user'
					);

				}

			}

			$message_type = substr( $type, 0, -1 );
			$response[] = array(
				'ok',
				/* translators: 1: users count 2: user type */
				esc_html( sprintf( __( '%1$d %2$s(s) import process has begun', 'wc-importer-for-reviso' ), $n, $message_type ) ),
			);

			echo json_encode( $response );

		}

		exit;
	}


}
new WCIFR_Users( true );

