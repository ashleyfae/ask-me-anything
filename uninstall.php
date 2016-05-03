<?php
/**
 * Uninstall Ask Me Anything
 *
 * Deletes all the plugin data, including:
 *      + Custom Post Types
 *      + Terms and Taxonomies
 *      + Plugin Options
 *      + Capabilities
 *      + Roles
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load AMA file.
include_once 'ask-me-anything.php';

global $wpdb, $wp_roles;

if ( ! ask_me_anything_get_option( 'delete_on_uninstall' ) ) {
	return;
}

/*
 * Delete all the custom post types
 */
$ama_taxonomies = array( 'question_categories' );
$ama_post_types = array( 'question' );
foreach ( $ama_post_types as $post_type ) {
	$ama_taxonomies = array_merge( $ama_taxonomies, get_object_taxonomies( $post_type ) );
	$items          = get_posts( array(
		'post_type'   => $post_type,
		'post_status' => 'any',
		'numberposts' => - 1,
		'fields'      => 'ids'
	) );
	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item, true );
		}
	}
}

/*
 * Delete all terms and taxonomies
 */
$get_terms_args = array(
	'taxonomy'   => $ama_taxonomies,
	'hide_empty' => false
);
$terms          = get_terms( $get_terms_args );

if ( is_array( $terms ) ) {
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, $term->taxonomy );
	}
}

/*
 * Delete all the plugin options
 */
$options = array(
	'ask_me_anything_settings',
	'ask_me_anything_version',
	'ask_me_anything_version_upgraded_from',
	'ask_me_anything_created_default_category'
);

foreach ( $options as $option ) {
	delete_option( $option );
}

/*
 * Delete Capabilities
 */
Ask_Me_Anything()->roles->remove_caps();

/*
 * Delete roles
 */
$ama_roles = array( 'question_manager' );
foreach ( $ama_roles as $role ) {
	remove_role( $role );
}

/*
 * Delete any other transients prefixed with 'ask_me_anything_'
 */
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ask_me_anything_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ask_me_anything_%'" );