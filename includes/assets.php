<?php
/**
 * Add the scripts and styles to the front-end.
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
 * Load Scripts
 *
 * @since 1.0
 * @return void
 */
function ask_me_anything_load_scripts() {

	if ( defined( 'ASK_ME_ANYTHING_DISABLE_JS' ) && ASK_ME_ANYTHING_DISABLE_JS === true ) {
		return;
	}

	$js_dir = ASK_ME_ANYTHING_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'ask-me-anything', $js_dir . 'front-end' . $suffix . '.js', array( 'jquery' ), ASK_ME_ANYTHING_VERSION );
	wp_enqueue_script( 'ask-me-anything' );

	wp_localize_script( 'ask-me-anything', 'ASK_ME_ANYTHING', apply_filters( 'ask-me-anything/javascript-vars', array(
		'ajaxurl' => admin_url( 'wp-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ama_submit_question' )
	) ) );

}