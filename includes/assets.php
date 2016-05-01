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

	wp_register_script( 'ask-me-anything', $js_dir . 'front-end' . $suffix . '.js', array(
		'jquery',
		'wp-util'
	), ASK_ME_ANYTHING_VERSION, true );
	wp_enqueue_script( 'ask-me-anything' );

	$description = ask_me_anything_get_option( 'form_desc', '' );

	wp_localize_script( 'ask-me-anything', 'ASK_ME_ANYTHING', apply_filters( 'ask-me-anything/javascript-vars', array(
		'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
		'display_questions'        => ask_me_anything_get_option( 'show_questions', true ),
		'nonce'                    => wp_create_nonce( 'ask_me_anything_nonce' ),
		'form_title_text'          => ask_me_anything_get_option( 'form_title', __( 'Ask Me Anything', 'ask-me-anything' ) ),
		'form_description'         => $description ? wpautop( $description ) : '',
		'form_require_name'        => ask_me_anything_get_option( 'require_name', false ),
		'form_require_email'       => ask_me_anything_get_option( 'require_email', false ),
		'form_question_field_name' => esc_html( ask_me_anything_get_option( 'question_field_name', __( 'Question', 'ask-me-anything' ) ) )
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

	wp_enqueue_style( 'ask-me-anything', $url, array(), ASK_ME_ANYTHING_VERSION, 'all' );
	wp_add_inline_style( 'ask-me-anything', ask_me_anything_generated_css() );

}

add_action( 'wp_enqueue_scripts', 'ask_me_anything_load_css' );

/**
 * Load Underscore.js Templates
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_underscores_templates() {
	ask_me_anything_get_template_part( 'questions' );
	ask_me_anything_get_template_part( 'submit-question', 'form' );
	ask_me_anything_get_template_part( 'single', 'question' );
}

add_action( 'wp_footer', 'ask_me_anything_underscores_templates' );

/**
 * Generate CSS based on "Styles" settings.
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_generated_css() {
	$css = '';

	$button_bg = ask_me_anything_get_option( 'button_bg_colour' );
	if ( $button_bg ) {
		$css .= '.ask-me-anything-button { background-color: ' . esc_attr( $button_bg ) . '; }';
	}

	$button_text = ask_me_anything_get_option( 'button_text_colour' );
	if ( $button_text ) {
		$css .= '.ask-me-anything-button { color: ' . esc_attr( $button_text ) . '; }';
	}

	return $css;
}