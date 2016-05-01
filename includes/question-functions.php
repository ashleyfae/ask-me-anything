<?php
/**
 * General functions for the 'question' post type and related
 * taxonomies.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Get Categories
 *
 * Fetches an array of all the categories, with an id=>name association.
 * These results are cached in a transient to make our load times nice
 * and snappy!
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_get_categories() {
	if ( false === ( $categories = get_transient( 'ask_me_anything_categories' ) ) ) {
		$categories = get_terms( array(
			'orderby'    => 'name',
			'hide_empty' => false,
			'fields'     => 'id=>name',
			'taxonomy'   => 'question_categories'
		) );
	}

	if ( empty( $categories ) || ! count( $categories ) ) {
		return array(
			'none' => __( 'Please create a category', 'ask-me-anything' )
		);
	}

	return $categories;
}

/**
 * Get Categories Dropdown
 *
 * Returns <option> tags for each available category. If there's only one category then
 * nothing is returned.
 *
 * @param string $selected
 *
 * @since 1.0.0
 * @return bool|string False if there's only one choice or less
 */
function ask_me_anything_get_categories_dropdown( $selected = '' ) {
	if ( empty( $selected ) ) {
		$selected = ask_me_anything_get_option( 'default_category', '' );
	}

	$categories = ask_me_anything_get_categories();

	if ( count( $categories ) <= 1 ) {
		return false;
	}

	$options = '';

	foreach ( $categories as $id => $name ) {
		$options .= '<option value="' . esc_attr( $id ) . '"' . selected( $selected, $id, false ) . '>' . esc_html( $name ) . '</option>';
	}

	return $options;
}