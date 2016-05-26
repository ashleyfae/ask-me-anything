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

	$question->notify_subscribers( $comment_object->comment_content, array( $comment_object->comment_author_email ) );

}

add_action( 'wp_insert_comment', 'ask_me_anything_notify_subscribers', 99, 2 );

/**
 * Get IP Address
 *
 * @since 1.0.2
 * @return string
 */
function ask_me_anything_get_ip() {
	$ip = $_SERVER['REMOTE_ADDR'] ?: ( $_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['HTTP_CLIENT_IP'] );

	return apply_filters( 'ask-me-anything/get-ip', $ip );
}

/**
 * Is Spam
 *
 * Integrates with Akismet to check for spam. If Akismet is not installed then
 * we automatically assume it's NOT spam.
 *
 * @param array $data
 *
 * @since 1.0.2
 * @return bool True if is spam
 */
function ask_me_anything_is_spam( $data = array() ) {
	if ( ! class_exists( 'Akismet' ) ) {
		return false;
	}

	if ( ! method_exists( 'Akismet', 'http_post' ) ) {
		return false;
	}

	$default_args = array(
		'comment_content' => '',
		'user_ip'         => ask_me_anything_get_ip(),
		'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null,
		'referrer'        => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null,
		'blog'            => get_option( 'home' ),
		'blog_lang'       => get_locale(),
		'blog_charset'    => get_option( 'blog_charset' ),
		'comment_type'    => 'contact-form'
	);

	if ( current_user_can( 'manage_options' ) ) {
		$default_args['user_role'] = 'administrator';
	}

	$args = wp_parse_args( $data, apply_filters( 'ask-me-anything/akismet/default-http-args', $default_args ) );

	$query_string = Akismet::build_query( $args );
	$response     = Akismet::http_post( apply_filters( 'ask-me-anything/akismet/http-query-string', $query_string ), 'comment-check' );
	$result       = ( is_array( $response ) && isset( $response[1] ) && $response[1] == 'true' ) ? true : false;

	return apply_filters( 'ask-me-anything/akismet/is-spam', $result, $response, $args );
}

/**
 * Change Spam Status
 *
 * @param string $path Either 'submit-spam' or 'submit-ham'
 *
 * @since 1.0.2
 * @return bool True on success, false on failure
 */
function ask_me_anything_change_spam_status( $data, $path = 'submit-spam' ) {
	if ( ! class_exists( 'Akismet' ) ) {
		return false;
	}

	if ( ! method_exists( 'Akismet', 'http_post' ) ) {
		return false;
	}

	$allowed_paths = array(
		'submit-spam',
		'submit-ham'
	);

	if ( ! in_array( $path, $allowed_paths ) ) {
		return false;
	}

	$default_args = array(
		'comment_content' => '',
		'user_ip'         => ask_me_anything_get_ip(),
		'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null,
		'referrer'        => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null,
		'blog'            => get_option( 'home' ),
		'blog_lang'       => get_locale(),
		'blog_charset'    => get_option( 'blog_charset' ),
		'comment_type'    => 'contact-form',
		'is_test'         => true //@todo remove
	);

	if ( current_user_can( 'manage_options' ) ) {
		$default_args['user_role'] = 'administrator';
	}

	$args = wp_parse_args( $data, apply_filters( 'ask-me-anything/akismet/change-spam-status/default-http-args', $default_args ) );

	$query_string = Akismet::build_query( $args );
	$response     = Akismet::http_post( apply_filters( 'ask-me-anything/akismet/change-spam-status/http-query-string', $query_string ), $path );
	$result       = ( is_array( $response ) && isset( $response[1] ) && $response[1] == 'Thanks for making the web a better place.' ) ? true : false;

	return apply_filters( 'ask-me-anything/akismet/change-spam-status/result', $result, $response, $args );
}