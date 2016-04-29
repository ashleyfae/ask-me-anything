<?php

/**
 * Admin Notices Class
 *
 * Handles displaying informational admin notices.
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
 * Class Ask_Me_Anything_Notices
 *
 * @since 1.0.0
 */
class Ask_Me_Anything_Notices {

	/**
	 * Ask_Me_Anything_Notices constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'ask-me-anything/dismiss/notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show Notices
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function show_notices() {
		$notices = array(
			'updated' => array(),
			'error'   => array()
		);

		if ( isset( $_GET['ask-me-anything-message'] ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				switch ( $_GET['ask-me-anything-message'] ) {
					case 'settings-imported' :
						$notices['updated']['ask-me-anything-settings-imported'] = __( 'The settings have been successfully imported.', 'ask-me-anything' );
						break;
				}
			}
		}

		if ( count( $notices['updated'] ) ) {
			foreach ( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'ask-me-anything-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) ) {
			foreach ( $notices['error'] as $notice => $message ) {
				add_settings_error( 'ask-me-anything-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'ask-me-anything-notices' );
	}

	/**
	 * Dismiss Notices
	 *
	 * Update current user's meta to mark this notice as dismissed.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function dismiss_notices() {
		if ( isset( $_GET['webinar_pages_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_ask_me_anything_' . $_GET['ask_me_anything_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'ask_me_anything_action', 'ask_me_anything_notice' ) ) );
			exit;
		}
	}

}

new Ask_Me_Anything_Notices;