<?php
/**
 * Register Post Statuses
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
 * Register Statuses
 *
 * Uses the statuses entered in the settings panel to create
 * new post statuses.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_register_statuses() {
	$statuses     = ask_me_anything_get_option( 'statuses', "Pending\nIn Progress\nCompleted" );
	$status_array = explode( "\n", $statuses );

	if ( ! count( $status_array ) ) {
		return;
	}

	foreach ( $status_array as $status ) {
		$key  = strtolower( ask_me_anything_sanitize_key( $status ) );
		$args = array(
			'label'                  => $status,
			'public'                 => true,
			'exclude_from_search'    => false,
			'show_in_admin_all_list' => true,
			'label_count'            => _n_noop( $status . ' <span class="count">(%s)</span>', $status . ' <span class="count">(%s)</span>' ),
		);

		register_post_status( $key, apply_filters( 'ask-me-anything/post-status/register-args', $args, $status ) );
	}
}

add_action( 'init', 'ask_me_anything_register_statuses' );

/**
 * Get Default Status
 *
 * Gets the first status entered in the list and returns the key
 * version of the name (all lowercase, no spaces, no symbols).
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_default_status() {
	$statuses     = ask_me_anything_get_option( 'statuses', "Pending\nIn Progress\nCompleted" );
	$status_array = explode( "\n", $statuses );

	// If we don't have any statuses, use "Pending".
	if ( ! count( $status_array ) || ! array_key_exists( 0, $status_array ) ) {
		return 'pending';
	}

	$default_status = $status_array[0];

	return apply_filters( 'ask-me-anything/post-status/default-key', strtolower( ask_me_anything_sanitize_key( $default_status ) ) );
}

/**
 * Get Default Status Name
 *
 * Gets the first statusu entered in the list and returns the name.
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_default_status_name() {
	$statuses     = ask_me_anything_get_option( 'statuses', "Pending\nIn Progress\nCompleted" );
	$status_array = explode( "\n", $statuses );

	// If we don't have any statuses, use "Pending".
	if ( ! count( $status_array ) || ! array_key_exists( 0, $status_array ) ) {
		return __( 'Pending', 'ask-me-anything' );
	}

	return apply_filters( 'ask-me-anything/post-status/default-name', $status_array[0] );
}