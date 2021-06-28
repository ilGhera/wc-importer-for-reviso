<?php
/**
 * WCIFR Temporary Data
 *
 * Handles temporary data coming from Reviso.
 *
 * @author ilGhera
 * @package wc-importer-for-reviso-premium/classes
 * @since 0.9.0
 */
class WCIFR_Temporary_Data {

	/**
	 * The constructor
	 *
	 * @param boolean $init true per eseguire hooks iniziali.
	 * @param  string  $type users or products.
	 *
	 * @return void
	 */
	public function __construct( $init = false, $type = false ) {

		if ( $init ) {

			$this->db_tables();

		}

		$this->type = $type;

	}


	/**
	 * Create the db table
	 *
	 * @return void
	 */
	public function db_tables() {

		global $wpdb;

		$temporary_users_data = $wpdb->prefix . 'wcifr_users_temporary_data';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$temporary_users_data'" ) != $temporary_users_data ) {

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $temporary_users_data (
				id 			bigint(20) NOT NULL AUTO_INCREMENT,
				hash        varchar(255) NOT NULL,
				data 		longtext NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $sql );

		}

	}


	/**
	 * The DB table name
	 *
	 * @return string
	 */
	private function table_name() {

		$output = 'wcifr_users_temporary_data';

		return $output;

	}


	/**
	 * Get data from the table
	 *
	 * @param  string $hash the hash code of the specific data.
	 *
	 * @return array
	 */
	public function get_data( $hash ) {

		global $wpdb;

		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name() . " WHERE hash = '$hash'";

		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( isset( $results[0]['data'] ) ) {

			return $results[0]['data'];

		}

	}


	/**
	 * Add temporary data to the table
	 *
	 * @param  string $hash the hash code of the specific data.
	 * @param  string $data the data.
	 *
	 * @return void
	 */
	public function add_data( $hash, $data ) {

		global $wpdb;

		$results = $this->get_data( $hash );

		if ( null == $results ) {

			$wpdb->insert(
				$wpdb->prefix . $this->table_name(),
				array(
					'hash' => $hash,
					'data' => $data,
				),
				array(
					'%s',
					'%s',
				)
			);

		}

	}


	/**
	 * Delete record from the table
	 *
	 * @param  string $hash the hash code of the specific data.
	 *
	 * @return void
	 */
	public function delete_data( $hash ) {

		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . $this->table_name(),
			array(
				'hash' => $hash,
			),
			array(
				'%s',
			)
		);

	}



}
new WCIFR_Temporary_Data( true );

