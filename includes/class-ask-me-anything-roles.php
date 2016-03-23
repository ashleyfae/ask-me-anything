<?php

/**
 * Class for setting up and managing roles.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ask_Me_Anything_Roles {

	/**
	 * Here we go...
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Add new question manager roles with default WP caps
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function add_roles() {
		add_role( 'question_manager', __( 'Question Manager', 'ask-me-anything' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true
		) );
	}

	/**
	 * Add new question-specific capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function add_caps() {

		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'question_manager', 'manage_ask_me_anything_settings' );
			$wp_roles->add_cap( 'administrator', 'manage_ask_me_anything_settings' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'question_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
		}

	}

	/**
	 * Gets the core post type capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps() {
		$capabilities     = array();
		$capability_types = array( 'question' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);
		}

		return $capabilities;
	}

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function remove_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->remove_cap( 'question_manager', 'manage_ask_me_anything_settings' );
			$wp_roles->remove_cap( 'administrator', 'manage_ask_me_anything_settings' );

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'question_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
				}
			}
		}
	}

}