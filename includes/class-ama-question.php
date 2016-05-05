<?php

/**
 * class-ama-question.php
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class AMA_Question {

	/**
	 * Question ID
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $ID = 0;

	/**
	 * Title of the question
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $title;

	/**
	 * Question status
	 *
	 * Same as post_status but used for transitions.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $status;

	/**
	 * Name of the question status (in a nice, readable form)
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $status_name;

	/**
	 * Array of category IDs
	 *
	 * @var array|bool
	 * @since 1.0.0
	 */
	private $category_id;

	/**
	 * Array of WP_Term objects
	 *
	 * @var array|bool
	 * @since 1.0.0
	 */
	private $category;

	/**
	 * Name of the person who submitted the question
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $submitter;

	/**
	 * Email of the person who submitted the question
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $submitter_email;

	/**
	 * Whether or not to notify the submitter of new comments
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	private $notify_submitter;

	/**
	 * Array of people who are subscribed to the question and want
	 * to be notified of new comments. Array contains email addresses.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $subscribers;

	/**
	 * Number of up votes
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $up_votes;

	/**
	 * Number of down votes
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $down_votes;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_payment_meta calls to be run
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $pending;

	/**
	 * Declare the default properities in WP_Post as we can't extend it
	 * Anything we've delcared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * AMA_Question constructor.
	 *
	 * @param int|bool $_id Post ID of the question
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not the question was successfully set up
	 */
	public function __construct( $_id = false ) {

		$question = WP_Post::get_instance( $_id );

		return $this->setup_question( $question );

	}

	/**
	 * Set the variables
	 *
	 * @param  WP_Post|int $question The post object or ID
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool If the setup was successful or not
	 */
	private function setup_question( $question ) {

		if ( is_numeric( $question ) ) {
			$question = WP_Post::get_instance( $question );
		}

		if ( ! is_object( $question ) ) {
			return false;
		}

		if ( ! is_a( $question, 'WP_Post' ) ) {
			return false;
		}

		if ( 'question' !== $question->post_type ) {
			return false;
		}

		foreach ( $question as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}

		}

		return true;

	}

	/**
	 * Magic SET function
	 *
	 * Sets up the pending array for the save method
	 *
	 * @param string $key   The property name
	 * @param mixed  $value The value of the property
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __set( $key, $value ) {
		$ignore = array( 'post_status' );

		if ( ! in_array( $key, $ignore ) ) {
			$this->pending[ $key ] = $value;
		}

		$this->$key = $value;
	}

	/**
	 * Insert Question
	 *
	 * Insert a new (fairly empty) question into the database.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return int|WP_Error
	 */
	private function insert_question() {

		$question_args = array(
			'post_title'   => $this->title,
			'post_status'  => ask_me_anything_get_default_status(),
			'post_type'    => 'question',
			'post_content' => $this->post_content
		);

		$question_id = wp_insert_post( apply_filters( 'ask-me-anything/question/insert-question-args', $question_args ) );

		if ( ! empty( $question_id ) ) {
			$this->ID = $question_id;
		}

		return $this->ID;

	}

	/**
	 * Save Question
	 *
	 * Saves question data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function save() {

		$saved = false;

		// We need to insert a new question.
		if ( empty( $this->ID ) ) {
			$question_id = $this->insert_question();

			if ( false === $question_id ) {
				$saved = false;
			} else {
				$this->ID = $question_id;
			}
		}

		if ( ! empty( $this->pending ) && is_array( $this->pending ) ) {
			$question_args = array(
				'ID' => $this->ID
			);

			foreach ( $this->pending as $key => $value ) {
				switch ( $key ) {
					case 'title' :
						$question_args['post_title'] = $this->title;
						break;

					case 'status' :
						do_action( 'ask-me-anything/question/transition-status', $this->post_status, $this->status, $this->ID );
						$question_args['post_status'] = $this->status;
						break;

					case 'category_id' :
						wp_set_post_terms( $this->ID, $this->category_id, 'question_categories', true );
						break;

					case 'submitter' :
						$this->update_meta( 'ama_submitter', $this->submitter );
						break;

					case 'submitter_email' :
						$this->update_meta( 'ama_submitter_email', $this->submitter_email );
						break;

					case 'notify_submitter' :
						$this->update_meta( 'ama_notify_submitter', $this->notify_submitter );
						break;

					case 'up_votes' :
						$this->update_meta( 'ama_up_votes', absint( $this->up_votes ) );
						break;

					case 'down_votes' :
						$this->update_meta( 'ama_down_votes', absint( $this->down_votes ) );
						break;

					case 'post_content' :
						$question_args['post_content'] = $this->post_content;
						break;

				}
			}

			if ( is_array( $question_args ) && count( $question_args ) > 1 ) {
				wp_update_post( $question_args );
			}

			$this->pending = array();
			$saved         = true;
		}

		if ( true === $saved ) {
			$this->setup_question( $this->ID );
		}

		return $saved;

	}

	/**
	 * Update Meta
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $prev_value
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|int
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {

		if ( empty( $meta_key ) ) {
			return false;
		}

		$meta_value = apply_filters( 'ask-me-anything/question/update-meta/' . $meta_key, $meta_value, $this->ID );

		return update_post_meta( $this->ID, $meta_key, $meta_value, $prev_value );

	}

	/**
	 * Get Question Title
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_title() {

		if ( ! isset( $this->title ) ) {
			$this->title = get_the_title( $this->ID );
		}

		return apply_filters( 'ask-me-anything/question/get/title', $this->title, $this->ID, $this );

	}

	/**
	 * Get Status Name
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_status_name() {

		if ( ! isset( $this->status_name ) ) {
			// Get all the statuses.
			$statuses = ask_me_anything_get_statuses();

			// If it's a valid status, get the name. Otherwise use "Pending".
			$this->status_name = array_key_exists( $this->post_status, $statuses ) ? $statuses[ $this->post_status ] : __( 'Pending', 'ask-me-anything' );
		}

		return apply_filters( 'ask-me-anything/question/get/status_name', $this->status_name, $this->ID, $this );

	}

	/**
	 * Get Status Class
	 *
	 * Returns the class name, but for use in an HTML class attribute.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_status_class() {

		$status_name = 'ama-status-' . $this->get_status_name();

		return apply_filters( 'ask-me-anything/question/get/status-class', strtolower( sanitize_html_class( $status_name ) ), $this->ID, $this );

	}

	/**
	 * Get Category ID
	 *
	 * Returns an array of category IDs.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|bool False if no category
	 */
	public function get_category_id() {

		if ( ! isset( $this->category_id ) ) {
			$category_id       = wp_get_post_terms( $this->ID, 'question_categories', array( 'fields' => 'ids' ) );
			$this->category_id = is_array( $category_id ) ? $category_id : false;
		}

		return apply_filters( 'ask-me-anything/question/get/category_id', $this->category_id, $this->ID, $this );

	}

	/**
	 * Get Category
	 *
	 * Returns an array of WP_Term objects.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|bool False if no category
	 */
	public function get_category() {

		if ( ! isset( $this->category ) ) {
			$categories     = wp_get_post_terms( $this->ID, 'question_categories' );
			$this->category = is_array( $categories ) ? $categories : false;
		}

		return apply_filters( 'ask-me-anything/question/get/category', $this->category, $this->ID, $this );

	}

	/**
	 * Get Submitter
	 *
	 * Returns the name of the person who submitted the question.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_submitter() {

		if ( ! isset( $this->submitter ) ) {
			$this->submitter = get_post_meta( $this->ID, 'ama_submitter', true );
		}

		return apply_filters( 'ask-me-anything/question/get/submitter', $this->submitter, $this->ID, $this );

	}

	/**
	 * Get Submitter Email
	 *
	 * Returns the email address of the person who submitted the question.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_submitter_email() {

		if ( ! isset( $this->submitter_email ) ) {
			$this->submitter_email = get_post_meta( $this->ID, 'ama_submitter_email', true );
		}

		return apply_filters( 'ask-me-anything/question/get/submitter_email', $this->submitter_email, $this->ID, $this );

	}

	/**
	 * Get Notify Submitter
	 *
	 * Whether or not the submitter would like to be notified about new responses
	 * to their question.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function get_notify_submitter() {

		if ( ! isset( $this->notify_submitter ) ) {
			$notify                 = get_post_meta( $this->ID, 'ama_notify_submitter', true );
			$this->notify_submitter = ! empty( $notify ) ? true : false;
		}

		return apply_filters( 'ask-me-anything/question/get/notify_submitter', $this->notify_submitter, $this->ID, $this );

	}

	/**
	 * Get Number of Up Votes
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_up_votes() {

		if ( ! isset( $this->up_votes ) ) {
			$this->up_votes = absint( get_post_meta( $this->ID, 'ama_up_votes', true ) );
		}

		return apply_filters( 'ask-me-anything/question/get/up_votes', $this->up_votes, $this->ID, $this );

	}

	/**
	 * Get Number of Down Votes
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_down_votes() {

		if ( ! isset( $this->down_votes ) ) {
			$this->down_votes = absint( get_post_meta( $this->ID, 'ama_down_votes', true ) );
		}

		return apply_filters( 'ask-me-anything/question/get/down_votes', $this->down_votes, $this->ID, $this );

	}

	/**
	 * Get Edit Link
	 *
	 * If the current user has permission to edit this question, then the edit
	 * link is returned. Otherwise, an empty string.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_edit_link() {

		if ( ! current_user_can( 'edit_question', $this->ID ) ) {
			$link = '';
		} else {
			$link = get_edit_post_link( $this->ID );
		}

		return apply_filters( 'ask-me-anything/question/get/edit_link', $link, $this->ID, $this );

	}

	/**
	 * Get Question Content
	 *
	 * Multiple filters are applied to the content.
	 * @see    /includes/question-functions.php
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_question() {

		return apply_filters( 'ask-me-anything/question/get/question', $this->post_content, $this->ID, $this );

	}

	/**
	 * Get Subscribers
	 *
	 * Returns an array of email addresses that are subscribed to the question and want to be
	 * notified when new comments are posted.
	 *
	 * @param bool $include_submitter Whether or not to include the submitter
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_subscribers( $include_submitter = true ) {

		$subscribers = get_post_meta( $this->ID, 'ama_subscribers', true );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( $include_submitter === true && is_email( $this->get_submitter_email() ) && $this->get_notify_submitter() ) {
			$subscribers[] = $this->get_submitter_email();
		}

		$this->subscribers = array_unique( $subscribers );

		return apply_filters( 'ask-me-anything/question/get/subscribers', $this->subscribers, $include_submitter, $this->ID, $this );

	}

	/**
	 * Get Number of Subscribers
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function get_number_subscribers() {

		return apply_filters( 'ask-me-anything/question/get/number_subscribers', count( $this->get_subscribers() ) );

	}

	/**
	 * Get Template Data
	 *
	 * Returns an array of all the data we need in the Underscore.js templates.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_template_data() {

		// If we have zero comments then we prompt them to leave a comment.
		$comments_title = __( 'Leave a Comment', 'ask-me-anything' );

		// If we have at least one comment then we change the title to show the number.
		if ( $this->comment_count > 0 ) {
			$comments_title = sprintf(
				esc_html( _nx( 'One Comment', '%1$s Comments', $this->comment_count, 'comments title', 'ask-me-anything' ) ),
				number_format_i18n( $this->comment_count )
			);
		}

		$question_data = array(
			'question_url'          => get_permalink( $this->ID ),
			'question_id'           => $this->ID,
			'question_status_class' => $this->get_status_class(),
			'question_status'       => $this->get_status_name(),
			'question_title'        => $this->get_title(),
			'question_content'      => $this->get_question(),
			'question_submitter'    => $this->get_submitter(),
			'number_comments'       => $this->comment_count,
			'number_up'             => $this->get_up_votes(),
			'number_down'           => $this->get_down_votes(),
			'question_edit_link'    => $this->get_edit_link(),
			'comments_title'        => $comments_title
		);

		return apply_filters( 'ask-me-anything/question/get/template-data', $question_data, $this->ID, $this );

	}

	/**
	 * Notify Subscribers
	 *
	 * Emails subscribers to notify them of a new comment on the question.
	 *
	 * @param array $exclude Array of email addresses to exclude from the notification
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not an email was sent
	 */
	public function notify_subscribers( $exclude = array() ) {

		$subscriber_array = $this->get_subscribers();

		if ( empty( $subscriber_array ) || ! is_array( $subscriber_array ) || ! count( $subscriber_array ) ) {
			return false;
		}

		$headers = array();

		foreach ( $subscriber_array as $email ) {
			if ( ! is_email( $email ) || in_array( $email, $exclude ) ) {
				continue;
			}

			$headers[] = sprintf( 'Bcc: %s', $email );
		}

		$subject = ask_me_anything_get_option( 'notification_subject', sprintf( __( 'New comment on question "%s"', 'ask-me-anything' ), '[subject]' ) );
		$message = ask_me_anything_get_option( 'notification_message', sprintf( __( 'A new comment has been posted on "%1$s":' . "\n\n" . '%2$s' . "\n\n" . 'Link: %3$s', 'ask-me-anything' ), '[subject]', '[message]', '[link]' ) );

		$find = array(
			'[subject]',
			'[message]',
			'[link]'
		);

		$replace = array(
			$this->get_title(),
			$this->get_question(),
			get_permalink( $this->ID )
		);

		// Find and replace placeholders.
		$subject = str_replace( apply_filters( 'ask-me-anything/question/notify-email/placeholders', $find ), apply_filters( 'ask-me-anything/question/notify-email/placeholder-values', $replace ), $subject );
		$message = str_replace( apply_filters( 'ask-me-anything/question/notify-email/placeholders', $find ), apply_filters( 'ask-me-anything/question/notify-email/placeholder-values', $replace ), $message );

		return wp_mail( '', sanitize_text_field( $subject ), wp_strip_all_tags( $message ), $headers );

	}

	/**
	 * Add Notify Email
	 *
	 * Adds a new email address to our array. This is the array of users we need to notify
	 * when new comments are added.
	 *
	 * @param string $email
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not the list was updated successfully
	 */
	public function add_notify_email( $email = '' ) {

		if ( ! is_email( $email ) ) {
			return false;
		}

		$subscribers       = $this->get_subscribers();
		$subscribers       = is_array( $subscribers ) ? $subscribers : array();
		$subscribers[]     = $email;
		$this->subscribers = $subscribers;

		return update_post_meta( $this->ID, 'ama_subscribers', $this->subscribers );

	}

	/**
	 * Remove Notify Email
	 *
	 * Removes an email from the notification list.
	 *
	 * @param string $email
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not the list was updated successfully
	 */
	public function remove_notify_email( $email = '' ) {

		if ( ! is_email( $email ) ) {
			return false;
		}

		$subscribers = $this->get_subscribers();
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( ( $key = array_search( $email, $subscribers ) ) !== false ) {
			unset( $subscribers[ $key ] );
		}

		$this->subscribers = $subscribers;

		return true;

	}

	/**
	 * Insert Comment
	 *
	 * Inserts a new comment for this question.
	 *
	 * @param array $comment_data
	 *
	 * @uses   AMA_Question::notify_subscribers()
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false ID of the new comment or false on failure
	 */
	public function insert_comment( $comment_data = array() ) {

		// Add some extra parameters.
		$comment_data['comment_post_ID'] = $this->ID;

		return wp_new_comment( $comment_data );

	}

	/**
	 * Get Comments
	 *
	 * Compiles an array of all comments connected to this question. Each comment
	 * array contains details about the comment. Including:
	 *      + Comment ID ('ID')
	 *      + Comment Author Name ('comment_author')
	 *      + Comment Author Email ('comment_author_email')
	 *      + Comment Date ('comment_date')
	 *      + Comment Content ('comment_content')
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|false False on failure
	 */
	public function get_comments() {

		$comments_data = array();
		$args          = array(
			'post_id' => $this->ID
		);

		$comments = get_comments( apply_filters( 'ask-me-anything/question/comments/query-args', $args ) );

		if ( ! is_array( $comments ) ) {
			return false;
		}

		foreach ( $comments as $comment ) {
			$data = $this->get_comment_data( $comment );

			if ( ! is_array( $data ) ) {
				continue;
			}

			$comments_data[] = $data;
		}

		return apply_filters( 'ask-me-anything/question/comments/comments-data', $comments_data );

	}

	/**
	 * Get Comment Data
	 *
	 * @param WP_Comment|int $comment Comment object or ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|false False on failure
	 */
	public function get_comment_data( $comment ) {

		if ( is_numeric( $comment ) ) {
			$comment = get_comment( $comment );
		}

		if ( empty( $comment ) || ! is_a( $comment, 'WP_Comment' ) ) {
			return false;
		}

		$data = array(
			'ID'                   => $comment->comment_ID,
			'avatar'               => get_avatar( $comment->comment_author, apply_filters( 'ask-me-anything/question/comments/avatar-size', 42 ), '', false, array( 'class' => 'ama-avatar' ) ),
			'comment_author'       => $comment->comment_author,
			'comment_author_email' => $comment->comment_author_email,
			'comment_author_url '  => $comment->comment_author_url,
			'comment_date'         => $comment->comment_date,
			'comment_content'      => $comment->comment_content
		);

		return apply_filters( 'ask-me-anything/question/comment-data', $data, $comment, $this->ID );

	}

}