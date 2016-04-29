<?php
/**
 * Ajax Callbacks
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

function ask_me_anything_get_questions() {
	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$page_number = absint( $_POST['page_number'] );
	$questions   = array();

	$query_args = array(
		'paged'          => $page_number,
		'posts_per_page' => absint( ask_me_anything_get_option( 'questions_per_page', 5 ) ),
		'post_type'      => 'question',
		'post_status'    => 'any'
	);

	$question_query = new WP_Query( apply_filters( 'ask-me-anything/get-questions/query-args', $query_args, $page_number ) );

	if ( ! $question_query->have_posts() ) {
		wp_send_json_error( __( 'No more questions.', 'ask-me-anything' ) );
	}

	while ( $question_query->have_posts() ) : $question_query->the_post();

		$question = get_post( get_the_ID() );

		$question_data = array(
			'question_url'          => get_permalink(),
			'question_id'           => get_the_ID(),
			'question_status_class' => '',
			'question_status'       => '',
			'question_title'        => get_the_title(),
			'number_comments'       => $question->comment_count,
			'number_up'             => absint( get_post_meta( $question->ID, 'ama_up_votes', true ) ),
			'number_down'           => absint( get_post_meta( $question->ID, 'ama_down_votes', true ) )
		);

		$questions[] = $question_data;

	endwhile;

	wp_reset_postdata();

	wp_send_json_success( $questions );
}

add_action( 'wp_ajax_ask_me_anything_get_questions', 'ask_me_anything_get_questions' );
add_action( 'wp_ajax_nopriv_ask_me_anything_get_questions', 'ask_me_anything_get_questions' );