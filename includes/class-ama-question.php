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
	 * Number of up votes
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $up_votes = 0;

	/**
	 * Number of down votes
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private $down_votes = 0;

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

					case 'submitter' :
						$this->update_meta( 'ama_submitter', $this->submitter );
						break;

					case 'submitter_email' :
						$this->update_meta( 'ama_submitter_email', $this->submitter_email );
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

		$status_name = $this->get_status_name();

		return apply_filters( 'ask-me-anything/question/get/status-class', strtolower( sanitize_html_class( $status_name ) ), $this->ID, $this );

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
	 * Get Template Data
	 *
	 * Returns an array of all the data we need in the Underscore.js templates.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_template_data() {

		$question_data = array(
			'question_url'          => get_permalink( $this->ID ),
			'question_id'           => $this->ID,
			'question_status_class' => $this->get_status_class(),
			'question_status'       => $this->get_status_name(),
			'question_title'        => $this->get_title(),
			'question_content'      => '',
			'number_comments'       => $this->comment_count,
			'number_up'             => $this->get_up_votes(),
			'number_down'           => $this->get_down_votes(),
			'question_edit_link'    => $this->get_edit_link()
		);

		return apply_filters( 'ask-me-anything/question/get/template-data', $question_data, $this->ID, $this );

	}

}