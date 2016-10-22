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
 * Uses the statuses entered in the settings panel to create new post statuses.
 *
 * @uses  ask_me_anything_get_statuses()
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_register_statuses() {
	$statuses = ask_me_anything_get_statuses();

	foreach ( $statuses as $key => $status ) {
		$args = array(
			'label'                  => $status,
			'public'                 => true,
			'exclude_from_search'    => false,
			'show_in_admin_all_list' => true,
			'label_count'            => _n_noop( $status . ' <span class="count">(%s)</span>', $status . ' <span class="count">(%s)</span>' ),
		);

		// The spam status should be private.
		if ( $key == 'ama_spam' ) {
			$args['public']                 = false;
			$args['exclude_from_search']    = true;
			$args['show_in_admin_all_list'] = false;
		}

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
 * @uses  ask_me_anything_get_statuses()
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_default_status() {
	$statuses    = ask_me_anything_get_statuses();
	$status_keys = array_keys( $statuses );

	return apply_filters( 'ask-me-anything/post-status/default-key', $status_keys[0] );
}

/**
 * Get Default Status Name
 *
 * Gets the first statusu entered in the list and returns the name.
 *
 * @uses  ask_me_anything_get_statuses()
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_default_status_name() {
	$statuses = ask_me_anything_get_statuses();

	return apply_filters( 'ask-me-anything/post-status/default-name', $statuses[0] );
}

/**
 * Get Statuses
 *
 * Returns an array of statuses. The key is the name of the status in
 * a sanitized key format. The value is the name.
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_get_statuses() {
	$statuses     = ask_me_anything_get_option( 'statuses', "Pending\nIn Progress\nCompleted" );
	$status_array = explode( "\n", $statuses );
	$final_array  = array();

	// If we don't have any statuses, use "Pending".
	if ( ! count( $status_array ) || ! array_key_exists( 0, $status_array ) ) {
		$final_array = array(
			'ama_pending' => esc_html__( 'Pending', 'ask-me-anything' )
		);
	} else {
		foreach ( $status_array as $status ) {
			$key = 'ama_' . strtolower( ask_me_anything_sanitize_key( $status ) );

			$final_array[ $key ] = esc_html( $status );
		}
	}

	$final_array['ama_spam'] = esc_html__( 'Spam', 'ask-me-anything' );

	return apply_filters( 'ask-me-anything/get-statuses', $final_array );
}

/**
 * Get Public Statuses
 *
 * Returns an array of public statuses only. For use on the front-end.
 * The 'ama_spam' status is excluded.
 *
 * @since 1.1.0
 * @return array
 */
function ask_me_anything_get_public_statuses() {
	$all_statuses = ask_me_anything_get_statuses();

	if ( array_key_exists( 'ama_spam', $all_statuses ) ) {
		unset( $all_statuses['ama_spam'] );
	}

	return apply_filters( 'ask-me-anything/get-public-statuses', $all_statuses );
}

/**
 * Save Question Status
 *
 * Whenever a status is saved, we make sure it's using a valid status.
 *
 * @param array   $data
 * @param WP_Post $post
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_save_question_status( $data, $post ) {
	if ( $data['post_type'] != 'question' ) {
		return $data;
	}

	// If we're trying to trash a request, let it go through.
	if ( $data['post_status'] == 'trash' ) {
		return $data;
	}

	// If this is an auto draft, let it go through.
	if ( $data['post_status'] == 'auto-draft' ) {
		return $data;
	}

	$allowed_statuses = array_keys( ask_me_anything_get_statuses() );

	if ( isset( $_POST['ama_status'] ) && in_array( $_POST['ama_status'], $allowed_statuses ) ) {
		$data['post_status'] = $_POST['ama_status'];
	} elseif ( in_array( $data['post_status'], $allowed_statuses ) ) {
		return $data;
	} else {
		$data['post_status'] = 'ama_pending';
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'ask_me_anything_save_question_status', 99, 2 );