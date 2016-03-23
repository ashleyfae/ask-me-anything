<?php

/**
 * Processes the activating and deactivating of license keys.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class Ask_Me_Anything_Updater {

	/**
	 * Constructor function.
	 *
	 * Adds the ajax actions.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_ask_me_anything_activate_license', array( $this, 'activate' ) );
		add_action( 'wp_ajax_ask_me_anything_deactivate_license', array( $this, 'deactivate' ) );
	}

	/**
	 * Activates a license key.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( __( 'Access denied.', 'ask-me-anything' ) );
		}

		$license          = isset( $_POST['license'] ) ? strip_tags( $_POST['license'] ) : '';
		$license_key_name = isset( $_POST['license_key_name'] ) ? $_POST['license_key_name'] : '';
		$product_name     = isset( $_POST['product_name'] ) ? $_POST['product_name'] : '';
		$status_name      = isset( $_POST['status_option_name'] ) ? $_POST['status_option_name'] : '';

		if ( empty( $license ) ) {
			wp_send_json_error( __( 'Error: Please fill out your license key.', 'ask-me-anything' ) );
		}

		if ( empty( $product_name ) || empty( $status_name ) ) {
			wp_send_json_error( __( 'An unexpected error has occurred.', 'ask-me-anything' ) );
		}

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $product_name ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg(
				$api_params,
				NOSE_GRAZE_STORE_URL
			),
			array(
				'timeout'   => 15,
				'sslverify' => false
			)
		);

		// If the response failed - bail.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'Error: Failed to activate.', 'ask-me-anything' ) );
		}

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( strip_tags( $status_name ), $license_data );

		if ( $license_data->license == 'valid' ) {
			// Save the license in the database.
			ask_me_anything_update_option( strip_tags( $license_key_name ), $license );

			// Send success.
			wp_send_json_success( sprintf( __( 'License activated successfully! You have %s activations remaining.', 'ask-me-anything' ), $license_data->activations_left ) );
		} else {
			switch ( $license_data->error ) {

				case 'expired' :
					wp_send_json_error( sprintf(
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'ask-me-anything' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ),
						'https://shop.nosegraze.com/checkout/?edd_license_key=' . $license . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
					) );
					break;

				case 'missing' :
					wp_send_json_error( sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify that you have the correct license.', 'ask-me-anything' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					) );
					break;

				case 'item_name_mismatch' :
					wp_send_json_error( sprintf(
						__( 'This is not the correct license for this product. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify that you have the correct license.', 'ask-me-anything' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=item_name_mismatch'
					) );
					break;

				case 'no_activations_left' :
					wp_send_json_error( sprintf(
						__( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'ask-me-anything' ),
						'https://shop.nosegraze.com/my-account/'
					) );
					break;

			}

			wp_send_json_error( __( 'Error: Invalid license key.', 'ask-me-anything' ) );
		}

		exit;
	}

	/**
	 * Deactivates a license key
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( __( 'Access denied.', 'ask-me-anything' ) );
		}

		$license          = isset( $_POST['license'] ) ? $_POST['license'] : '';
		$license_key_name = isset( $_POST['license_key_name'] ) ? $_POST['license_key_name'] : '';
		$product_name     = isset( $_POST['product_name'] ) ? $_POST['product_name'] : '';
		$status_name      = isset( $_POST['status_option_name'] ) ? $_POST['status_option_name'] : '';

		if ( empty( $license ) ) {
			wp_send_json_error( __( 'Error: Please fill out your license key.', 'ask-me-anything' ) );
		}

		if ( empty( $product_name ) || empty( $status_name ) ) {
			wp_send_json_error( __( 'An unexpected error has occurred.', 'ask-me-anything' ) );
		}

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( $product_name ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg(
				$api_params,
				NOSE_GRAZE_STORE_URL
			),
			array(
				'timeout'   => 15,
				'sslverify' => false
			)
		);

		// If the response failed - bail.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'Error: Failed to activate.' ) );
		}

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'deactivated' ) {
			delete_option( strip_tags( $status_name ) );
			ask_me_anything_delete_option( strip_tags( $license_key_name ) );
			wp_send_json_success( __( 'License deactivated successfully!', 'ask-me-anything' ) );
		} else {
			wp_send_json_error( __( 'Error: License not deactivated. Please contact support.', 'ask-me-anything' ) );
		}
	}

}

new Ask_Me_Anything_Updater();