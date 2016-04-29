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
 * Load JavaScript on the front-end
 *
 * @since 1.0.0
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

add_action( 'wp_enqueue_scripts', 'ask_me_anything_load_scripts' );

/**
 * Load CSS on the front-end
 *
 * Themes can override this CSS file by creating a folder in their theme
 * called 'ask-me-anything' and putting a file inside called
 * ask-me-anything.css.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_load_css() {

	if ( ask_me_anything_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file         = 'ask-me-anything' . $suffix . '.css';
	$template_dir = ask_me_anything_get_theme_template_dir_name();
	$url          = '';

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $template_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $template_dir . 'ask-me-anything.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $template_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $template_dir . 'ask-me-anything.css';
	$ama_plugin_style_sheet     = trailingslashit( ask_me_anything_get_templates_dir() ) . $file;

	/*
	 * Look in the child theme directory first, followed by the parent theme, followed by the AMA core templates directory.
	 * Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	 * This allows users to copy just ask-me-anything.css to their theme
	 */
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $template_dir . 'ask-me-anything.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $template_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $template_dir . 'ask-me-anything.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $template_dir . $file;
		}
	} elseif ( file_exists( $ama_plugin_style_sheet ) || file_exists( $ama_plugin_style_sheet ) ) {
		$url = trailingslashit( ask_me_anything_get_templates_url() ) . $file;
	}

	// If we still can't find a URL at this point, bail.
	if ( empty( $url ) ) {
		return;
	}

	wp_register_style( 'ask-me-anything', $url, array(), ASK_ME_ANYTHING_VERSION, 'all' );
	wp_enqueue_style( 'ask-me-anything' );

}

add_action( 'wp_enqueue_scripts', 'ask_me_anything_load_css' );