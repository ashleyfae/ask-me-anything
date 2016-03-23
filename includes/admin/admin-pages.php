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