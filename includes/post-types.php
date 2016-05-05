<?php
/**
 * Functions for registering post types and taxonomies.
 *
 * Post Type:
 *  + question
 *
 * Taxonomy:
 *  + question_statuses
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
 * Registers the Question post type.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_setup_post_types() {

	$archives = defined( 'ASK_ME_ANYTHING_DISABLE_ARCHIVE' ) && ASK_ME_ANYTHING_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'ASK_ME_ANYTHING_SLUG' ) ? ASK_ME_ANYTHING_SLUG : 'questions';
	$rewrite  = defined( 'ASK_ME_ANYTHING_DISABLE_REWRITE' ) && ASK_ME_ANYTHING_DISABLE_REWRITE ? false : array(
		'slug'       => $slug,
		'with_front' => false
	);

	// Set up the labels.
	$question_labels = apply_filters( 'ask-me-anything/cpt/question-labels', array(
		'name'                  => _x( '%2$s', 'question post type name', 'ask-me-anything' ),
		'singular_name'         => _x( '%1$s', 'singular question post type name', 'ask-me-anything' ),
		'add_new'               => __( 'Add New', 'ask-me-anything' ),
		'add_new_item'          => __( 'Add New %1$s', 'ask-me-anything' ),
		'edit_item'             => __( 'Edit %1$s', 'ask-me-anything' ),
		'new_item'              => __( 'New %1$s', 'ask-me-anything' ),
		'all_items'             => __( 'All %2$s', 'ask-me-anything' ),
		'view_item'             => __( 'View %1$s', 'ask-me-anything' ),
		'search_items'          => __( 'Search %2$s', 'ask-me-anything' ),
		'not_found'             => __( 'No %2$s found', 'ask-me-anything' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'ask-me-anything' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'question post type menu name', 'ask-me-anything' ),
		'featured_image'        => __( '%1$s Featured Image', 'ask-me-anything' ),
		'set_featured_image'    => __( 'Set %1$s Featured Image', 'ask-me-anything' ),
		'remove_featured_image' => __( 'Remove %1$s Featured Image', 'ask-me-anything' ),
		'use_featured_image'    => __( 'Use as %1$s Featured Image', 'ask-me-anything' ),
	) );

	foreach ( $question_labels as $key => $value ) {
		$question_labels[ $key ] = sprintf( $value, ask_me_anything_get_label_singular(), ask_me_anything_get_label_plural() );
	}

	$question_args = array(
		'labels'             => $question_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => $rewrite,
		'capability_type'    => 'question',
		'map_meta_cap'       => true,
		'menu_icon'          => 'dashicons-editor-help',
		'has_archive'        => $archives,
		'hierarchical'       => true,
		'supports'           => apply_filters( 'ask-me-anything/cpt/question-supports', array(
			'title',
			'editor',
			'comments'
		) ),
	);

	register_post_type( 'question', apply_filters( 'ask-me-anything/cpt/question-args', $question_args ) );

}

add_action( 'init', 'ask_me_anything_setup_post_types', 1 );

/**
 * Remove 'comments' from array of supported features.
 *
 * This gets removed if the "Allow Comments" setting is unchecked.
 *
 * @param array $supports
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_remove_comments_support( $supports ) {
	if ( ! ask_me_anything_get_option( 'comments_on_questions' ) && ( $key = array_search( 'comments', $supports ) ) !== false ) {
		unset( $supports[ $key ] );
	}

	return $supports;
}

add_filter( 'ask-me-anything/cpt/question-supports', 'ask_me_anything_remove_comments_support' );

/**
 * Make CPT Private
 *
 * If 'show questions on front-end' is disabled then we need to make the whole CPT private.
 * This disables the single-question pages.
 *
 * @param array $args
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_make_cpt_private( $args ) {
	if ( ! ask_me_anything_get_option( 'show_questions' ) ) {
		$args['public']             = false;
		$args['publicly_queryable'] = false;
	}

	return $args;
}

add_filter( 'ask-me-anything/cpt/question-args', 'ask_me_anything_make_cpt_private' );

/**
 * Get Default Labels
 *
 * @since 1.0.0
 * @return array $defaults Default labels
 */
function ask_me_anything_get_default_labels() {
	$defaults = array(
		'singular' => __( 'Question', 'ask-me-anything' ),
		'plural'   => __( 'Questions', 'ask-me-anything' )
	);

	return apply_filters( 'ask-me-anything/cpt/question-default-labels', $defaults );
}

/**
 * Get Singular Label
 *
 * @param bool $lowercase Whether or not the result should be in lowercase.
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_label_singular( $lowercase = false ) {
	$defaults = ask_me_anything_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_get_label_plural( $lowercase = false ) {
	$defaults = ask_me_anything_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Register Taxonomies
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_setup_taxonomies() {

	$slug = defined( 'ASK_ME_ANYTHING_SLUG' ) ? ASK_ME_ANYTHING_SLUG : 'questions';

	/* Question Categories */
	$category_labels = array(
		'name'              => sprintf( _x( '%s Categories', 'taxonomy general name', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'singular_name'     => sprintf( _x( '%s Category', 'taxonomy singular name', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'ask-me-anything' ), ask_me_anything_get_label_singular() ),
		'menu_name'         => __( 'Categories', 'ask-me-anything' ),
	);
	$category_args   = apply_filters( 'ask-me-anything/taxonomy/category-args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'ask-me-anything/taxonomy/category-labels', $category_labels ),
			'show_ui'      => true,
			'query_var'    => 'question_categories',
			'rewrite'      => array( 'slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
		)
	);
	register_taxonomy( 'question_categories', array( 'question' ), $category_args );
	register_taxonomy_for_object_type( 'question_categories', 'question' );

}

add_action( 'init', 'ask_me_anything_setup_taxonomies', 0 );