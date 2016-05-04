<?php
/**
 * Functions for the admin pages.
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
 * Creates admin submenu pages under 'Questions'.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_add_options_link() {
	$ama_settings_page = add_submenu_page( 'edit.php?post_type=question', __( 'Ask Me Anything Settings', 'ask-me-anything' ), __( 'Settings', 'ask-me-anything' ), 'manage_ask_me_anything_settings', 'ask-me-anything-settings', 'ask_me_anything_options_page' );
}

add_action( 'admin_menu', 'ask_me_anything_add_options_link', 10 );

/**
 * Is Admin Page
 *
 * Checks whether or not the current page is an Ask Me Anything admin page.
 *
 * @since 1.0.0
 * @return bool
 */
function ask_me_anything_is_admin_page() {
	$screen      = get_current_screen();
	$is_ama_page = false;

	if ( $screen->base == 'question_page_ask-me-anything-settings' ) {
		$is_ama_page = true;
	}

	if ( ( $screen->base == 'post' || $screen->base == 'edit' ) && $screen->post_type == 'question' ) {
		$is_ama_page = true;
	}

	return apply_filters( 'ask-me-anything/is-admin-page', $is_ama_page, $screen );
}

/**
 * Load Admin Scripts
 *
 * Adds all admin scripts and stylesheets to the admin panel.
 *
 * @param string $hook Currently loaded page
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_load_admin_scripts( $hook ) {
	if ( ! apply_filters( 'ask-me-anything/load-admin-scripts', ask_me_anything_is_admin_page(), $hook ) ) {
		return;
	}

	$js_dir  = ASK_ME_ANYTHING_PLUGIN_URL . 'assets/js/';
	$css_dir = ASK_ME_ANYTHING_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	/*
	 * JavaScript
	 */

	// Media Upload
	/*wp_enqueue_media();
	wp_register_script( 'ask-me-anything-media-upload', $js_dir . 'media-upload' . $suffix . '.js', array( 'jquery' ), ASK_ME_ANYTHING_VERSION, true );
	wp_enqueue_script( 'ask-me-anything-media-upload' );*/

	$settings = array(
		'text_title'  => __( 'Upload or Select an Image', 'ask-me-anything' ),
		'text_button' => __( 'Insert Image', 'ask-me-anything' )
	);

	wp_localize_script( 'ask-me-anything-media-upload', 'AMA_MEDIA', apply_filters( 'ask-me-anything/media-upload-js-settings', $settings ) );

	$admin_deps = array(
		'jquery',
		'wp-color-picker'
	);

	wp_register_script( 'ask-me-anything-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, ASK_ME_ANYTHING_VERSION, true );
	wp_enqueue_script( 'ask-me-anything-admin-scripts' );

	$settings = array(
		'text_remove' => __( 'Remove', 'ask-me-anything' )
	);

	wp_localize_script( 'ask-me-anything-admin-scripts', 'ASK_ME_ANYTHING', apply_filters( 'ask-me-anything/admin-scripts-settings', $settings ) );

	/*
	 * Stylesheets
	 */
	wp_enqueue_style( 'ask-me-anything-admin', $css_dir . 'ask-me-anything-admin' . $suffix . '.css', ASK_ME_ANYTHING_VERSION );
	wp_add_inline_style( 'ask-me-anything-admin', ask_me_anything_generated_css() );
	wp_enqueue_style( 'wp-color-picker' );
}

add_action( 'admin_enqueue_scripts', 'ask_me_anything_load_admin_scripts', 100 );