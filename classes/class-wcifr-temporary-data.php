<?php
/**
 * WCIFR Temporary Data
 *
 * Handles temporary data coming from Reviso.
 *
 * @author ilGhera
 * @package wc-importer-for-reviso-premium/classes
 * @since 1.3.6
 */
class WCIFR_Temporary_Data {

	/**
	 * The constructor
	 *
	 * @param boolean $init true per eseguire hooks iniziali
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			$this->wcifr_db_tables();

		}

	}

	/**
	 * Create the db table 
	 *
	 * @return void
	 */
	public function wcifr_db_tables() {

		global $wpdb;

		$temporary_data   = $wpdb->prefix . 'wcifr_temporary_data';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$temporary_data'" ) != $temporary_data ) {

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $temporary_data (
				id 			bigint(20) NOT NULL AUTO_INCREMENT,
				hash        varchar(255) NOT NULL,
				data 		longtext NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );

		}

	}


	/**
	 * Get data from the table
	 *
	 * @param  string $hash the hash code of the specific data. 
     *
	 * @return array
	 */
	public function wcifr_get_temporary_data( $hash ) {

		global $wpdb;

		$query = 'SELECT * FROM ' . $wpdb->prefix . "wcifr_temporary_data WHERE hash = '$hash'";

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
	public function wcifr_add_temporary_data( $hash, $data ) {

		global $wpdb;

		$results = $this->wcifr_get_temporary_data( $hash );

		if ( null == $results ) {

			$wpdb->insert(
				$wpdb->prefix . 'wcifr_temporary_data',
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
	public function wcifr_delete_temporary_data( $hash ) {

		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'wcifr_temporary_data',
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

