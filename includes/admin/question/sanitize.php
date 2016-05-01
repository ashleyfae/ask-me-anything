<?php
/**
 * Sanitize Meta Fields
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
 * Sanitize text fields.
 */
add_filter( 'ask-me-anything/meta-box/sanitize/ama_submitter', 'sanitize_text_field' );
add_filter( 'ask-me-anything/meta-box/sanitize/ama_submitter_email', 'sanitize_text_field' );

/**
 * Sanitize Checkbox
 *
 * Value can either be true (checked) or false (unchecked).
 *
 * @since 1.0.0
 * @return bool
 */
function ask_me_anything_sanitize_checkbox( $input ) {
	return ! empty( $input ) ? true : false;
}

add_filter( 'ask-me-anything/meta-box/sanitize/ama_notify_submitter', 'ask_me_anything_sanitize_checkbox' );