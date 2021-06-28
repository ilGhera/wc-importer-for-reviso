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

			add_action( 'wp_ajax_wcifr-import-users', array( $this, 'import_users' ) );
			add_action( 'wp_ajax_wcifr-get-customers-groups', array( $this, 'get_customers_groups' ) );
			add_action( 'wp_ajax_wcifr-get-suppliers-groups', array( $this, 'get_suppliers_groups' ) );
			add_action( 'wcifr_import_single_user_event', array( $this, 'import_single_user' ), 10, 3 );

		}

		$this->temporary_data = new WCIFR_Temporary_Data( false, 'users' );
		$this->wcifr_call     = new WCIFR_Call();

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
		echo wp_json_encode( $output );

		exit;

	}


	/**
	 * Callback - Get customers groups
	 */
	public function get_customers_groups() {

		$output = $this->get_user_groups( 'customers' );
		echo wp_json_encode( $output );

		exit;

	}


	/**
	 * Get province
	 *
	 * @param  string $url the endpoint.
	 *
	 * @return string
	 */
	private function get_province( $url ) {

		$endpoint = explode( 'https://rest.reviso.com/', $url );

		if ( isset( $endpoint[1] ) ) {

			$province = $this->wcifr_call->call( 'get', $endpoint[1] );

			if ( isset( $province->code ) ) {

				return $province->code;

			}
		}

	}


	/**
	 * Prepare the single user data to import in WordPress
	 *
	 * @param  string $user_data the Reviso user data json encoded.
	 *
	 * @return array
	 */
	public function prepare_user_data( $user_data ) {

		$get_data  = json_decode( $user_data );
		$data      = isset( $get_data->data ) ? $get_data->data : null;
		$user_role = isset( $get_data->role ) ? $get_data->role : null;

		if ( isset( $data->name, $data->email ) ) {

			$name_parts = explode( ' ', $data->name );

			$args = array(
				'role'         => $user_role,
				'user_login'   => strtolower( str_replace( ' ', '-', $data->name ) ),
				'user_email'   => $data->email,
				'first_name'   => $name_parts[0],
				'last_name'    => $name_parts[1],
				'display_name' => isset( $data->name ) ? $data->name : null,
				'user_url'     => isset( $data->website ) ? $data->website : null,

			);

			/* Update user if already exists */
			$existing_user = get_user_by( 'email', $data->email );

			if ( isset( $existing_user->ID ) ) {

				$args['ID'] = $existing_user->ID;

			} else {

				$args['user_pass'] = wp_generate_password();

			}

			$extras = array(
				'billing_first_name'    => $name_parts[0],
				'billing_last_name'     => $name_parts[1],
				'billing_country'       => isset( $data->country ) ? $data->country : null,
				'billing_city'          => isset( $data->city ) ? $data->city : null,
				'billing_state'         => isset( $data->province ) ? $this->get_province( $data->province->self ) : null,
				'billing_address_1'     => isset( $data->address ) ? $data->address : null,
				'billing_postcode'      => isset( $data->zip ) ? $data->zip : null,
				'billing_phone'         => isset( $data->phone ) ? $data->phone : null,
				'billing_wcefr_piva'    => isset( $data->vatNumber ) ? $data->vatNumber : null,
				'billing_wcefr_cf'      => isset( $data->corporateIdentificationNumber ) ? $data->corporateIdentificationNumber : null,
				'billing_wcefr_pec'     => isset( $data->italianCertifiedEmail ) ? $data->italianCertifiedEmail : null,
				'billing_wcefr_pa_code' => isset( $data->publicEntryNumber ) ? $data->publicEntryNumber : null,
			);

			return array(
				'args'   => $args,
				'extras' => $extras,
			);

		}

	}


	/**
	 * Import single user from Reviso
	 *
	 * @param  string $hash the hash code of the specific data.
	 *
	 * @return void
	 */
	public function import_single_user( $hash ) {

		$temp_data = $this->temporary_data->get_data( $hash );
		$data      = $this->prepare_user_data( $temp_data );

		if ( isset( $data['args'] ) && is_array( $data['args'] ) ) {

			$user_id = wp_insert_user( $data['args'] );

			if ( ! is_wp_error( $user_id ) && is_numeric( $user_id ) ) {

				if ( isset( $data['extras'] ) && is_array( $data['extras'] ) ) {

					foreach ( $data['extras'] as $key => $value ) {

						update_user_meta( $user_id, $key, $value );

					}
				}
			} else {

				error_log( 'WCIFR ERROR | Users | ' . $user_id->get_error_message() );

			}
		}

		/* Delete temporary data */
		$this->temporary_data->delete_data( $hash );

	}


	/**
	 * Import Reviso users as customers/ suppliers
	 *
	 * @return void
	 */
	public function import_users() {

		if ( isset( $_POST['wcifr-import-users-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcifr-import-users-nonce'] ), 'wcifr-import-users' ) ) {

			$type   = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$role   = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
			$groups = isset( $_POST['groups'] ) && is_array( $_POST['groups'] ) ? sanitize_array( $_POST['groups'] ) : ''; // Temp.

			/*Salvo le impostazioni nel database*/
			update_option( 'wcifr-' . $type . '-role', $role );
			update_option( 'wcifr-' . $type . '-groups', $groups ); // Temp.

			$results = $this->wcifr_call->call( 'get', $type );

			if ( isset( $results->collection ) && is_array( $results->collection ) ) {

				$count         = count( $results->collection );
				$current_user  = wp_get_current_user();
				$current_email = isset( $current_user->user_email ) ? $current_user->user_email : null;

				foreach ( $results->collection as $user_data ) {

					if ( isset( $user_data->email ) && $user_data->email !== $current_email ) {

						$data = array(
							'role' => $role,
							'data' => $user_data,
						);

						$hash = md5( wp_json_encode( $data ) );

						/* Add temporary data to the db table */
						$this->temporary_data->add_data( $hash, wp_json_encode( $data ) );

						/*Schedule single event*/
						as_enqueue_async_action(
							'wcifr_import_single_user_event',
							array(
								'hash' => $hash,
							),
							'wcifr_import_single_user'
						);

					}
				}

				$message_type = substr( $type, 0, -1 );
				$response[]   = array(
					'ok',
					/* translators: 1: users count 2: user type */
					esc_html( sprintf( __( '%1$d %2$s(s) import process has begun', 'wc-importer-for-reviso' ), $count, $message_type ) ),
				);

				echo wp_json_encode( $response );

			}
		}

		exit;

	}

}
new WCIFR_Users( true );

