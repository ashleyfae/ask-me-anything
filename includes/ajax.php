<?php
/**
 * Ajax Callbacks
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Get Questions
 *
 * Loads information for all questions on a given page into an array.
 *
 * @since 1.0.0
 * @return void
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

		$question = new AMA_Question( get_the_ID() );

		if ( $question->ID !== 0 ) {
			$questions[] = $question->get_template_data();
		}

	endwhile;

	wp_reset_postdata();

	wp_send_json_success( $questions );

}

add_action( 'wp_ajax_ask_me_anything_get_questions', 'ask_me_anything_get_questions' );
add_action( 'wp_ajax_nopriv_ask_me_anything_get_questions', 'ask_me_anything_get_questions' );

/**
 * Load Question
 *
 * Loads the information for an individual question.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_load_question() {

	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$question_id = absint( $_POST['question_id'] );
	$question    = new AMA_Question( $question_id );

	if ( $question->ID === 0 ) {
		wp_send_json_error( __( 'Error: Invalid question.', 'ask-me-anything' ) );
	}

	wp_send_json_success( $question->get_template_data() );

}

add_action( 'wp_ajax_ask_me_anything_load_question', 'ask_me_anything_load_question' );
add_action( 'wp_ajax_nopriv_ask_me_anything_load_question', 'ask_me_anything_load_question' );

function ask_me_anything_submit_question() {

	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$fields = $_POST['formData'];

	// No fields - bail.
	if ( empty( $fields ) || ! is_array( $fields ) ) {
		wp_send_json_error( __( 'Error: No field data.', 'ask-me-anything' ) );
	}

	do_action( 'ask-me-anything/ajax/submit-question', $fields );

	exit;

	//wp_send_json_success( '<p>Hi!</p>' );

}

add_action( 'wp_ajax_ask_me_anything_submit_question', 'ask_me_anything_submit_question' );
add_action( 'wp_ajax_nopriv_ask_me_anything_submit_question', 'ask_me_anything_submit_question' );

function ask_me_anything_insert_question( $fields ) {

	$question = new AMA_Question();
	$error    = new WP_Error();

	foreach ( $fields as $field_info ) {
		if ( ! array_key_exists( 'name', $field_info ) || ! array_key_exists( 'value', $field_info ) ) {
			continue;
		}

		switch ( $field_info['name'] ) {

			// Name
			case 'ask-me-anything-name' :
				if ( ask_me_anything_get_option( 'require_name', false ) && empty( $field_info['value'] ) ) {
					$error->add( 'empty-name', __( 'Error: The name field is required.', 'ask-me-anything' ) );
				} else {
					$question->submitter = sanitize_text_field( $field_info['value'] );
				}
				break;

			case 'ask-me-anything-email' :
				if ( ask_me_anything_get_option( 'require_email', false ) && empty( $field_info['value'] ) ) {
					$error->add( 'empty-email', __( 'Error: The email field is required.', 'ask-me-anything' ) );
				} else {
					$question->submitter_email = sanitize_text_field( $field_info['value'] );
				}
				break;

			case 'ask-me-anything-subject' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-subject', __( 'Error: The subject field is required.', 'ask-me-anything' ) );
				} else {
					$question->title = sanitize_text_field( wp_strip_all_tags( $field_info['value'] ) );
				}
				break;

			case 'ask-me-anything-question' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-message', sprintf( __( 'Error: The %s field is required.', 'ask-me-anything' ), strtolower( ask_me_anything_get_option( 'question_field_name', __( 'Question', 'ask-me-anything' ) ) ) ) );
				} else {
					$question->post_content = wp_kses_post( $field_info['value'] );
				}
				break;

		}
	}

	$question->save();

}

add_action( 'ask-me-anything/ajax/submit-question', 'ask_me_anything_insert_question' );