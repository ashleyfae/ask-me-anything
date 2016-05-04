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
		'post_status'    => array_keys( ask_me_anything_get_statuses() )
	);

	$question_query = new WP_Query( apply_filters( 'ask-me-anything/get-questions/query-args', $query_args, $page_number ) );

	if ( ! $question_query->have_posts() ) {
		wp_send_json_error( '<div class="ama-no-questions">' . ask_me_anything_get_option( 'no_questions_message', __( 'No questions yet!', 'ask-me-anything' ) ) . '</div>' );
	}

	while ( $question_query->have_posts() ) : $question_query->the_post();

		$question = new AMA_Question( get_the_ID() );

		if ( $question->ID !== 0 ) {
			$questions[] = $question->get_template_data();
		}

	endwhile;

	$final_output = array(
		'questions' => $questions
	);

	// Get next page and previous page.
	$final_output['next_page']     = ( $question_query->max_num_pages > $page_number ) ? ( $page_number + 1 ) : 0;
	$final_output['previous_page'] = ( $page_number > 1 ) ? ( $page_number - 1 ) : 0;

	wp_reset_postdata();

	wp_send_json_success( $final_output );

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

/**
 * Submit Question
 *
 * @see   ask_me_anything_insert_question()
 *
 * @since 1.0.0
 * @return void
 */
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

}

add_action( 'wp_ajax_ask_me_anything_submit_question', 'ask_me_anything_submit_question' );
add_action( 'wp_ajax_nopriv_ask_me_anything_submit_question', 'ask_me_anything_submit_question' );

/**
 * Insert Question
 *
 * @param array $fields
 */
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
					$error->add( 'empty-name', __( 'The name field is required.', 'ask-me-anything' ) );
				} else {
					$question->submitter = sanitize_text_field( $field_info['value'] );
				}
				break;

			case 'ask-me-anything-email' :
				if ( ask_me_anything_get_option( 'require_email', false ) && empty( $field_info['value'] ) ) {
					$error->add( 'empty-email', __( 'The email field is required.', 'ask-me-anything' ) );
				} elseif ( ask_me_anything_get_option( 'require_email', false ) && ! is_email( $field_info['value'] ) ) {
					$error->add( 'empty-email', __( 'Please enter a valid email address.', 'ask-me-anything' ) );
				} else {
					$question->submitter_email = sanitize_text_field( $field_info['value'] );
				}
				break;

			case 'ask-me-anything-category' :
				$category_id           = ! empty( $field_info['value'] ) ? absint( $field_info['value'] ) : ask_me_anything_get_option( 'default_category' );
				$question->category_id = is_array( $category_id ) ? $category_id : array( $category_id ); // Exepcting an array
				break;

			case
			'ask-me-anything-subject' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-subject', __( 'The subject field is required.', 'ask-me-anything' ) );
				} else {
					$question->title = sanitize_text_field( wp_strip_all_tags( $field_info['value'] ) );
				}
				break;

			case 'ask-me-anything-notify' :
				$question->notify_submitter = ! empty( $field_info['value'] ) ? true : false;
				break;

			case
			'ask-me-anything-question' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-message', sprintf( __( 'The %s field is required.', 'ask-me-anything' ), strtolower( ask_me_anything_get_option( 'question_field_name', __( 'Question', 'ask-me-anything' ) ) ) ) );
				} else {
					$question->post_content = wp_kses_post( $field_info['value'] );
				}
				break;

		}
	}

	// Oops, we have errors. Bail.
	if ( $error->get_error_codes() ) {
		$output = '<ul>';
		foreach ( $error->get_error_codes() as $code ) {
			$output .= '<li><strong>' . __( 'Error:', 'ask-me-anything' ) . '</strong> ' . esc_html( $error->get_error_message( $code ) ) . '</li>';
		}
		$output .= '</ul>';

		wp_send_json_error( $output );
	}

	$question->up_votes   = 0;
	$question->down_votes = 0;

	$result = $question->save();

	if ( false === $result ) {
		wp_send_json_error( __( 'An unexpected error occurred.', 'ask-me-anything' ) );
	}

	wp_send_json_success( ask_me_anything_get_option( 'form_success', __( 'Success! Your question has been submitted. I\'ll answer it as soon as I can!', 'ask-me-anything' ) ) );

	exit;

}

add_action( 'ask-me-anything/ajax/submit-question', 'ask_me_anything_insert_question' );

/**
 * Load Comments
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_load_comments() {

	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$question_id = absint( $_POST['question_id'] );
	$question    = new AMA_Question( $question_id );

	if ( $question->ID === 0 ) {
		wp_send_json_error( __( 'Error: Invalid question.', 'ask-me-anything' ) );
	}

	$comments = $question->get_comments();

	if ( is_array( $comments ) ) {
		wp_send_json_success( $comments );
	}

	wp_send_json_error( __( 'No comments.', 'ask-me-anything' ) );

}

add_action( 'wp_ajax_ask_me_anything_load_comments', 'ask_me_anything_load_comments' );
add_action( 'wp_ajax_nopriv_ask_me_anything_load_comments', 'ask_me_anything_load_comments' );

/**
 * Submit Comment
 *
 * Adds a new comment to a question. Returns comment data so we can inject it into the template.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_submit_comment() {

	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$question_id  = absint( $_POST['question_id'] );
	$question     = new AMA_Question( $question_id );
	$error        = new WP_Error();
	$fields       = $_POST['formData'];
	$comment_data = array(
		'comment_post_ID' => $question->ID
	);
	$notify_me    = false;

	// No fields - bail.
	if ( empty( $fields ) || ! is_array( $fields ) ) {
		wp_send_json_error( __( 'Error: No comment field data.', 'ask-me-anything' ) );
	}

	if ( $question->ID === 0 ) {
		wp_send_json_error( __( 'Error: Invalid question.', 'ask-me-anything' ) );
	}

	foreach ( $fields as $field_info ) {
		if ( ! array_key_exists( 'name', $field_info ) || ! array_key_exists( 'value', $field_info ) ) {
			continue;
		}

		switch ( $field_info['name'] ) {

			// Name
			case 'ama_comment_name' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-name', __( 'The name field is required.', 'ask-me-anything' ) );
				} else {
					$comment_data['comment_author'] = sanitize_text_field( $field_info['value'] );
				}
				break;

			// Email
			case 'ama_comment_email' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-email', __( 'The email field is required.', 'ask-me-anything' ) );
				} elseif ( ! is_email( $field_info['value'] ) ) {
					$error->add( 'invalid-email', __( 'Invalid email address.', 'ask-me-anything' ) );
				} else {
					$comment_data['comment_author_email'] = sanitize_text_field( $field_info['value'] );
				}
				break;

			// Comment
			case 'ama_comment' :
				if ( empty( $field_info['value'] ) ) {
					$error->add( 'empty-comment', __( 'The comment field is required.', 'ask-me-anything' ) );
				} else {
					$comment_data['comment_content'] = wp_kses_post( $field_info['value'] );
				}
				break;

			// Notify Me
			case 'ama_comment_notify' :
				if ( ! empty( $field_info['value'] ) ) {
					$notify_me = true;
				}
				break;

		}
	}

	// Oops, we have errors. Bail.
	if ( $error->get_error_codes() ) {
		$output = '<ul>';
		foreach ( $error->get_error_codes() as $code ) {
			$output .= '<li><strong>' . __( 'Error:', 'ask-me-anything' ) . '</strong> ' . esc_html( $error->get_error_message( $code ) ) . '</li>';
		}
		$output .= '</ul>';

		wp_send_json_error( $output );
	}

	// Add the comment.
	$comment_id = $question->insert_comment( $comment_data );

	if ( empty( $comment_id ) ) {
		wp_send_json_error( __( 'Error inserting comment.', 'ask-me-anything' ) );
	}

	$new_comment_data = $question->get_comment_data( $comment_id );

	// Notify people.
	$question->notify_subscribers( $new_comment_data['comment_author_email'] );

	// Maybe update notify list to add this email.
	if ( $notify_me === true ) {
		$question->add_notify_email( $new_comment_data['comment_author_email'] );
	}

	// Build success message.
	$output = array(
		'comment_data' => array( $new_comment_data ),
		'message'      => __( 'Your comment has been added successfully!', 'ask-me-anything' )
	);

	wp_send_json_success( apply_filters( 'ask-me-anything/question/comment/successfully-added', $output ) );

}

add_action( 'wp_ajax_ask_me_anything_submit_comment', 'ask_me_anything_submit_comment' );
add_action( 'wp_ajax_nopriv_ask_me_anything_submit_comment', 'ask_me_anything_submit_comment' );

/**
 * Vote on a Question
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_vote() {

	// Security check.
	check_ajax_referer( 'ask_me_anything_nonce', 'nonce' );

	$question_id = absint( $_POST['question_id'] );
	$vote_type   = $_POST['vote_type'];
	$vote_type   = ( $vote_type == 'up' ) ? 'up' : 'down';
	$question    = new AMA_Question( $question_id );

	if ( $question->ID == 0 ) {
		wp_send_json_error( __( 'Error: Invalid question.', 'ask-me-anything' ) );
	}

	if ( $vote_type == 'up' ) {
		$existing_votes     = $question->get_up_votes();
		$new_votes          = ( (int) $existing_votes + 1 );
		$question->up_votes = $new_votes;
	} else {
		$existing_votes       = $question->get_down_votes();
		$new_votes            = ( (int) $existing_votes + 1 );
		$question->down_votes = $new_votes;
	}

	$result = $question->save();

	if ( $result ) {
		wp_send_json_success( $new_votes );
	}

	wp_send_json_error( __( 'Error saving vote.', 'ask-me-anything' ) );

}

add_action( 'wp_ajax_ask_me_anything_vote', 'ask_me_anything_vote' );
add_action( 'wp_ajax_nopriv_ask_me_anything_vote', 'ask_me_anything_vote' );