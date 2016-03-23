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

if ( ask_me_anything_get_option( 'delete_on_uninstall' ) ) {

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

	foreach ( array_unique( array_filter( $ama_taxonomies ) ) as $taxonomy ) {
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}
		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
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

}