<?php
/**
 * Privacy Functions
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 * @since     1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register privacy exporters.
 *
 * @param array $exporters
 *
 * @since 1.1.3
 * @return array
 */
function ama_register_privacy_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Ask Me Anything Questions', 'ask-me-anything' ),
		'callback'               => 'ama_privacy_export_questions',
	);

	return $exporters;

}

add_filter( 'wp_privacy_personal_data_exporters', 'ama_register_privacy_exporters' );

/**
 * Retrieve AMA questions for the privacy data exporter.
 *
 * @param string $email_address Email address being requested.
 * @param int    $page          Page number.
 *
 * @since 1.1.3
 * @return array
 */
function ama_privacy_export_questions( $email_address = '', $page = 1 ) {

	$questions = get_posts( array(
		'post_type'      => 'question',
		'posts_per_page' => - 1,
		'post_status'    => array_keys( ask_me_anything_get_statuses() ),
		'meta_query'     => array(
			array(
				'key'   => 'ama_submitter_email',
				'value' => $email_address
			)
		)
	) );

	if ( empty( $questions ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_items = array();

	foreach ( $questions as $question ) {
		$data_points = array(
			array(
				'name'  => __( 'Name', 'ask-me-anything' ),
				'value' => get_post_meta( $question->ID, 'ama_submitter', true )
			),
			array(
				'name'  => __( 'Email', 'ask-me-anything' ),
				'value' => get_post_meta( $question->ID, 'ama_submitter_email', true )
			),
			array(
				'name'  => __( 'Email', 'ask-me-anything' ),
				'value' => get_post_meta( $question->ID, 'ama_submitter_email', true )
			),
			array(
				'name'  => __( 'Question Content', 'ask-me-anything' ),
				'value' => $question->post_content
			)
		);

		$export_items[] = array(
			'group_id'    => 'ama-questions',
			'group_label' => sprintf( __( 'Question: %s', 'ubb-rev-req' ), get_the_title( $question ) ),
			'item_id'     => 'ama-question-' . $question->ID,
			'data'        => $data_points
		);
	}

	return array( 'data' => $export_items, 'done' => true );

}