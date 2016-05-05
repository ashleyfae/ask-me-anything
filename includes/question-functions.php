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

/**
 * Apply filters to the question content.
 *
 * We apply all the same filters that 'the_content' gets, but we don't just want
 * to apply 'the_content' since plugins typically use this filter to append/preppend
 * social media links, post signatures, etc.
 *
 * @since 1.0.0
 */
add_filter( 'ask-me-anything/question/get/question', 'wptexturize' );
add_filter( 'ask-me-anything/question/get/question', 'convert_smilies' );
add_filter( 'ask-me-anything/question/get/question', 'convert_chars' );
add_filter( 'ask-me-anything/question/get/question', 'wpautop' );
add_filter( 'ask-me-anything/question/get/question', 'shortcode_unautop' );
add_filter( 'ask-me-anything/question/get/question', 'prepend_attachment' );

/**
 * Notify Subscribers
 *
 * Runs whenever a new comment is submitted. If the comment is on a 'question' then we
 * notify the subscribers about it.
 *
 * @param int        $comment_id
 * @param WP_Comment $comment_object
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_notify_subscribers( $comment_id, $comment_object ) {

	$question = new AMA_Question( $comment_object->comment_post_ID );

	if ( $question->ID === 0 ) {
		return;
	}

	$question->notify_subscribers( array( $comment_object->comment_author_email ) );

}

add_action( 'wp_insert_comment', 'ask_me_anything_notify_subscribers', 99, 2 );