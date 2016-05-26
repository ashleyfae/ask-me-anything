<?php
/**
 * "All Questions" Columns
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
 * Question Columns
 *
 * Defines the custom columns for the "All Questions" page.
 *
 * @param array $question_columns
 *
 * @since 1.0.0
 * @return array Updated array of columns
 */
function ask_me_anything_question_columns( $question_columns ) {
	$question_columns = array(
		'cb'                           => '<input type="checkbox"/>',
		'title'                        => esc_html__( 'Subject', 'ask-me-anything' ),
		'status'                       => esc_html__( 'Status', 'ask-me-anything' ),
		'taxonomy-question_categories' => esc_html__( 'Category', 'ask-me-anything' ),
		'up_votes'                     => esc_html__( 'Up Votes', 'ask-me-anything' ),
		'down_votes'                   => esc_html__( 'Down Votes', 'ask-me-anything' ),
		'date'                         => esc_html__( 'Date', 'ask-me-anything' )
	);

	return apply_filters( 'ask-me-anything/cpt/question-columns', $question_columns );
}

add_filter( 'manage_edit-question_columns', 'ask_me_anything_question_columns' );

/**
 * Render Question Columns
 *
 * @param string $column_name Name of the current column
 * @param int    $post_id     ID of the post
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_render_question_columns( $column_name, $post_id ) {

	if ( get_post_type( $post_id ) != 'question' ) {
		return;
	}

	$question = new AMA_Question( $post_id );

	switch ( $column_name ) {

		case 'status' :
			echo '<span class="ask-me-anything-status ' . esc_attr( $question->get_status_class() ) . '"><a href="' . esc_url( admin_url( 'edit.php?post_status=' . $question->post_status . '&post_type=question' ) ) . '">' . $question->get_status_name() . '</a></span>';
			break;

		case 'up_votes' :
			echo $question->get_up_votes();
			break;

		case 'down_votes' :
			echo $question->get_down_votes();
			break;

	}

}

add_action( 'manage_question_posts_custom_column', 'ask_me_anything_render_question_columns', 10, 2 );

/**
 * Register Sortable Columns
 *
 * @param array $sortable_columns
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_sortable_columns( $sortable_columns ) {
	$sortable_columns['up_votes']   = 'ama_up_votes';
	$sortable_columns['down_votes'] = 'ama_down_votes';

	return $sortable_columns;
}

add_filter( 'manage_edit-question_sortable_columns', 'ask_me_anything_sortable_columns' );

/**
 * Query Sortable Columns
 *
 * @param WP_Query $query
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_query_sortable_columns( $query ) {

	if ( ! $query->is_main_query() || ! $query->get( 'orderby' ) ) {
		return;
	}

	switch ( $query->get( 'orderby' ) ) {

		case 'ama_up_votes' :
			$query->set( 'meta_key', 'ama_up_votes' );
			$query->set( 'orderby', 'meta_value_num' );
			break;

		case 'ama_down_votes' :
			$query->set( 'meta_key', 'ama_down_votes' );
			$query->set( 'orderby', 'meta_value_num' );
			break;

	}

}

add_action( 'pre_get_posts', 'ask_me_anything_query_sortable_columns' );

function ask_me_anything_row_actions( $actions, $post ) {
	if ( $post->post_type != 'question' ) {
		return $actions;
	}

	$spam_action = ( $post->post_status == 'ama_spam' ) ? 'unspam' : 'spam';
	$spam_label  = ( $post->post_status == 'ama_spam' ) ? esc_html__( 'Not Spam', 'ask-me-anything' ) : esc_html__( 'Spam', 'ask-me-anything' );

	$actions['ama_spam'] = '<a href="#" class="ama-mark-spam ama-' . sanitize_html_class( $spam_action ) . '" data-action="' . esc_attr( $spam_action ) . '" data-question-id="' . esc_attr( $post->ID ) . '">' . $spam_label . '</a>';

	return $actions;
}

add_filter( 'post_row_actions', 'ask_me_anything_row_actions', 10, 2 );