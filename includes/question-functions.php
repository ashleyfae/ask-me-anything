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
		$categories = get_terms( 'question_categories', array(
			'orderby'    => 'name',
			'hide_empty' => false,
			'fields'     => 'id=>name'
		) );
	}

	if ( empty( $categories ) || ! count( $categories ) ) {
		return array(
			'none' => __( 'Please create a category', 'ask-me-anything' )
		);
	}

	return $categories;
}