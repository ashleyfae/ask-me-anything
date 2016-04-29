<?php

/**
 * License handler for Ask Me Anything
 *
 * Used to add, activate, deactivate, and manage license keys.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ask_Me_Anything_License
 *
 * @since 1.0.0
 */
class Ask_Me_Anything_License {

	private $file;
	private $license;
	private $item_name;
	private $item_id;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://shop.nosegraze.com/edd-sl-api/';

	/**
	 * Webinar_Pages_License constructor.
	 *
	 * @param string     $_file    Location of add-on file
	 * @param string|int $_item    Add-on name or ID number
	 * @param string     $_version Current version of add-on
	 * @param string     $_author  Name of author
	 * @param string     $_optname
	 * @param string     $_api_url
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct( $_file, $_item, $_version, $_author, $_optname = null, $_api_url = null ) {

		$this->file = $_file;

		if ( is_numeric( $_item ) ) {
			$this->item_id = absint( $_item );
		} else {
			$this->item_name = $_item;
		}

		$this->item_shortname = 'ask_me_anything_pages_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( ask_me_anything_get_option( $this->item_shortname . '_license_key', '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		$this->includes();
		$this->hooks();

	}

	/**
	 * Include the plugin updater class.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function includes() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once 'EDD_SL_Plugin_Updater.php';
		}

	}

	/**
	 * Setup hooks
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'ask-me-anything/settings/licenses', array( $this, 'settings' ), 1 );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		// Check that license is valid once per week
		add_action( 'ask-me-anything/events/weekly', array( $this, 'weekly_license_check' ) );

		// For testing license notices, uncomment this line to force checks on every page load
		//add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array(
			$this,
			'plugin_row_license_missing'
		), 10, 2 );

	}

	/**
	 * Auto Updater
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function auto_updater() {

		$args = array(
			'version' => $this->version,
			'license' => $this->license,
			'author'  => $this->author
		);

		if ( ! empty( $this->item_id ) ) {
			$args['item_id'] = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Set up the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			$args
		);

	}

	/**
	 * Add license field to settings
	 *
	 * @param array $settings Existing settings
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function settings( $settings ) {

		$ama_license_settings = array(
			'main' => array(
				$this->item_shortname . '_license_key' => array(
					'id'      => $this->item_shortname . '_license_key',
					'name'    => sprintf( __( '%s License Key', 'ask-me-anything' ), $this->item_name ),
					'desc'    => '',
					'type'    => 'license_key',
					'options' => array(
						'is_valid_license_option' => $this->item_shortname . '_license_active'
					),
					'size'    => 'regular'
				)
			)
		);

		if ( array_key_exists( 'main', $settings ) ) {
			$new_settings = array_merge( $settings['main'], $ama_license_settings['main'] );
		} else {
			$new_settings = $ama_license_settings['main'];
		}


		return array( 'main' => $new_settings );

	}

	/**
	 * Activate License Key
	 *
	 * Run after entering a license key and saving the settings.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function activate_license() {

		if ( ! isset( $_POST['ask_me_anything_settings'] ) ) {
			return;
		}

		if ( ! isset( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ] ) || ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_ask_me_anything_settings' ) ) {
			return;
		}

		// License key has been deleted - remove the status.
		if ( empty( $_POST['ask_me_anything_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			delete_option( $this->item_shortname . '_license_active' );

			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		$details = get_option( $this->item_shortname . '_license_active' );

		// If key is already active - bail.
		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['ask_me_anything_settings'][ $this->item_shortname . '_license_key' ] );

		if ( empty( $license ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}

	/**
	 * Deactivate License
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['ask_me_anything_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['ask_me_anything_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'ask-me-anything' ), __( 'Error', 'ask-me-anything' ), array( 'response' => 403 ) );
		}

		if ( ! current_user_can( 'manage_ask_me_anything_settings' ) ) {
			return;
		}

		if ( ! isset( $_POST[ $this->item_shortname . '_license_key_deactivate' ] ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $this->license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		delete_option( $this->item_shortname . '_license_active' );

	}

	/**
	 * Check if license key is valid once per week
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function weekly_license_check() {

		if ( ! empty( $_POST['ask_me_anything_settings'] ) ) {
			return; // Don't fire when saving settings
		}

		if ( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $this->license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}

	/**
	 * Admin Notices
	 *
	 * Displays any errors.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function notices() {

		static $showed_invalid_message;

		if ( empty( $this->license ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_ask_me_anything_settings' ) ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {
			if ( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
				$messages[] = sprintf(
					__( 'You have invalid or expired license keys for %1$s. Please go to the <a href="%2$s" title="Go to Licenses page">Licenses page</a> to correct this issue.', 'ask-me-anything' ),
					$this->item_name,
					admin_url( 'edit.php?post_type=question&page=ask-me-anything-settings&tab=licenses' )
				);

				$showed_invalid_message = true;
			}
		}

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
			}
		}

	}

	/**
	 * Displays message inline on plugin row that the license key i smissing
	 *
	 * @param $plugin_data
	 * @param $version_info
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_missing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_missing_key_message[ $this->item_shortname ] ) ) {
			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=question&page=ask-me-anything-settings&tab=licenses' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'ask-me-anything' ) . '</a></strong>';
			$showed_missing_key_message[ $this->item_shortname ] = true;
		}

	}

}