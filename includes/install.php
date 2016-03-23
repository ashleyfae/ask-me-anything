<?php
/**
 * Functions that run on plugin install.
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
 * Install
 *
 * Registers post types, custom taxonomies, and flushes
 * rewrite rules.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_install( $network_wide = false ) {
	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			ask_me_anything_run_install();
			restore_current_blog();
		}
	} else {
		ask_me_anything_run_install();
	}
}

register_activation_hook( ASK_ME_ANYTHING_PLUGIN_FILE, 'ask_me_anything_install' );

/**
 * Run Installation
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_run_install() {
	global $ask_me_anything_options;

	// Set up Custom Post Type.
	ask_me_anything_setup_post_types();

	// Set up Taxonomies.
	ask_me_anything_setup_taxonomies();

	// Clear permalinks.
	flush_rewrite_rules( false );

	// Add Upgraded from Option
	$current_version = get_option( 'ask_me_anything_version' );
	if ( $current_version ) {
		update_option( 'ask_me_anything_version_upgraded_from', $current_version );
	}

	// Set up our default settings.
	$options         = array();
	$current_options = get_option( 'ask_me_anything_settings', array() );

	// If we haven't already created our default taxonomy term - let's do it.
	if ( ! get_option( 'ask_me_anything_created_default_category' ) && ! array_key_exists( 'default_category', $current_options ) ) {
		$term = wp_insert_term( __( 'Question', 'ask-me-anything' ), 'question_categories' );

		if ( ! is_wp_error( $term ) && is_array( $term ) && array_key_exists( 'term_id', $term ) ) {
			$options['default_category'] = $term['term_id'];

			update_option( 'ask_me_anything_created_default_category', true );
		}
	}

	// Populate default values.
	foreach ( ask_me_anything_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {

			// Check for backwards compatibility
			$tab_sections = ask_me_anything_get_settings_tab_sections( $tab );
			if ( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section  = 'main';
				$settings = $sections;
			}

			foreach ( $settings as $option ) {
				if ( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}
			}
		}
	}

	$merged_options          = array_merge( $ask_me_anything_options, $options );
	$ask_me_anything_options = $merged_options;

	update_option( 'ask_me_anything_settings', $merged_options );
	update_option( 'ask_me_anything_version', ASK_ME_ANYTHING_VERSION );

	// Create book manager role.
	$roles = new Ask_Me_Anything_Roles();
	$roles->add_roles();
	$roles->add_caps();

	// Add the transient to redirect.
	set_transient( '_ask_me_anything_activation_redirect', true, 30 );
}

/**
 * When a new Blog is created in multisite, see if Ask Me Anything is network activated, and run the installer.
 *
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if ( is_plugin_active_for_network( plugin_basename( ASK_ME_ANYTHING_PLUGIN_FILE ) ) ) {
		switch_to_blog( $blog_id );
		ask_me_anything_install();
		restore_current_blog();
	}
}

add_action( 'wpmu_new_blog', 'ask_me_anything_new_blog_created', 10, 6 );