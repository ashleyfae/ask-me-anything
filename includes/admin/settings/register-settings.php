<?php
/**
 * Register Settings
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
 * Get an Option
 *
 * Looks to see if the specified setting exists, returns the default if not.
 *
 * @param string $key     Key to retrieve
 * @param mixed  $default Default option
 *
 * @global       $ask_me_anything_options
 *
 * @since 1.0.0
 * @return mixed
 */
function ask_me_anything_get_option( $key = '', $default = false ) {
	global $ask_me_anything_options;

	$value = ! empty( $ask_me_anything_options[ $key ] ) ? $ask_me_anything_options[ $key ] : $default;
	$value = apply_filters( 'ask-me-anything/options/get', $value, $key, $default );

	return apply_filters( 'ask-me-anything/options/get/' . $key, $value, $key, $default );
}

/**
 * Update an Option
 *
 * Updates an existing setting value in both the DB and the global variable.
 * Passing in an empty, false, or null string value will remove the key from the ask_me_anything_settings array.
 *
 * @param string $key   Key to update
 * @param mixed  $value The value to set the key to
 *
 * @global       $ask_me_anything_options
 *
 * @since 1.0.0
 * @return bool True if updated, false if not
 */
function ask_me_anything_update_option( $key = '', $value = false ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = ask_me_anything_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'ask_me_anything_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'ask-me-anything/options/update', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'ask_me_anything_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $ask_me_anything_options;
		$ask_me_anything_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an Option
 *
 * Removes an setting value in both the DB and the global variable.
 *
 * @param string $key The key to delete.
 *
 * @global       $ask_me_anything_options
 *
 * @since 1.0.0
 * @return boolean True if updated, false if not.
 */
function ask_me_anything_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'ask_me_anything_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'ask_me_anything_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $ask_me_anything_options;
		$ask_me_anything_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array Ask Me Anything settings
 */
function ask_me_anything_get_settings() {
	$settings = get_option( 'ask_me_anything_settings' );

	return apply_filters( 'ask-me-anything/get-settings', $settings );
}

/**
 * Add all settings sections and fields.
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_register_settings() {

	if ( false == get_option( 'ask_me_anything_settings' ) ) {
		add_option( 'ask_me_anything_settings' );
	}

	foreach ( ask_me_anything_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			add_settings_section(
				'ask_me_anything_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'ask_me_anything_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'ask_me_anything_settings[' . $option['id'] . ']',
					$name,
					function_exists( 'ask_me_anything_' . $option['type'] . '_callback' ) ? 'ask_me_anything_' . $option['type'] . '_callback' : 'ask_me_anything_missing_callback',
					'ask_me_anything_settings_' . $tab . '_' . $section,
					'ask_me_anything_settings_' . $tab . '_' . $section,
					array(
						'section'     => $section,
						'id'          => isset( $option['id'] ) ? $option['id'] : null,
						'desc'        => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'        => isset( $option['name'] ) ? $option['name'] : null,
						'size'        => isset( $option['size'] ) ? $option['size'] : null,
						'options'     => isset( $option['options'] ) ? $option['options'] : '',
						'std'         => isset( $option['std'] ) ? $option['std'] : '',
						'min'         => isset( $option['min'] ) ? $option['min'] : null,
						'max'         => isset( $option['max'] ) ? $option['max'] : null,
						'step'        => isset( $option['step'] ) ? $option['step'] : null,
						'chosen'      => isset( $option['chosen'] ) ? $option['chosen'] : null,
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null
					)
				);
			}
		}
	}

	// Creates our settings in the options table
	register_setting( 'ask_me_anything_settings', 'ask_me_anything_settings', 'ask_me_anything_settings_sanitize' );

}

add_action( 'admin_init', 'ask_me_anything_register_settings' );

/**
 * Registered Settings
 *
 * Sets and returns the array of all plugin settings.
 * Developers can use the following filters to add their own settings or
 * modify existing ones:
 *
 *  + ask-me-anything/settings/{key} - Where {key} is a specific tab. Used to modify a single tab/section.
 *  + ask-me-anything/settings/registered-settings - Includes the entire array of all settings.
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_get_registered_settings() {

	$ask_me_anything_settings = array(
		/* Question Settings */
		'questions' => apply_filters( 'ask-me-anything/settings/questions', array(
			'main'          => array(
				'display_position'      => array(
					'id'      => 'display_position',
					'name'    => __( 'Automatic Display', 'ask-me-anything' ),
					'desc'    => sprintf( __( 'Choose where to automatically display the question form. Choose "Do Not Display" to not automatically add the form to your site. You can still display it manually using the shortcode %s', 'ask-me-anything' ), '<code>[ask-me-anything]</code>' ),
					'type'    => 'select',
					'options' => array(
						'left'         => __( 'Left', 'ask-me-anything' ),
						'right'        => __( 'Right', 'ask-me-anything' ),
						'bottom-left'  => __( 'Bottom Left', 'ask-me-anything' ),
						'bottom-right' => __( 'Bottom Right', 'ask-me-anything' ),
						'none'         => __( 'Do Not Display', 'ask-me-anything' )
					),
					'std'     => 'bottom-right'
				),
				'visibility'            => array(
					'id'      => 'visibility',
					'name'    => __( 'Visibility', 'ask-me-anything' ),
					'desc'    => '',
					'type'    => 'select',
					'options' => array(
						'all'      => __( 'Everyone', 'ask-me-anything' ),
						'loggedin' => __( 'Logged In Users Only', 'ask-me-anything' ),
					),
					'std'     => 'all'
				),
				'show_questions'        => array(
					'id'   => 'show_questions',
					'name' => __( 'Show Questions on Front-End', 'ask-me-anything' ),
					'desc' => __( 'Check this to display the questions publicly. This allows your viewers to read through questions submitted by others.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => '1'
				),
				'questions_per_page'    => array(
					'id'         => 'questions_per_page',
					'name'       => __( 'Questions Per Page', 'ask-me-anything' ),
					'desc'       => __( 'Only applies on the front-end if questions are displayed. This number of questions will be shown on each page.', 'ask-me-anything' ),
					'type'       => 'text',
					'input-type' => 'number',
					'std'        => '5'
				),
				'comments_on_questions' => array(
					'id'   => 'comments_on_questions',
					'name' => __( 'Allow Comments on Questions', 'ask-me-anything' ),
					'desc' => __( 'Check this to allow readers to post comments on questions.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => '1'
				),
				'voting'                => array(
					'id'      => 'voting',
					'name'    => __( 'Voting', 'ask-me-anything' ),
					'desc'    => '',
					'type'    => 'select',
					'options' => array(
						'all'  => __( 'Allow Up Vote and Down Vote', 'ask-me-anything' ),
						'up'   => __( 'Up Vote Only', 'ask-me-anything' ),
						'down' => __( 'Down Vote Only', 'ask-me-anything' ),
						'none' => __( 'No Voting', 'ask-me-anything' )
					),
					'std'     => 'all'
				),
				'default_category'      => array(
					'id'      => 'default_category',
					'name'    => __( 'Default Category', 'ask-me-anything' ),
					'desc'    => __( 'This is the category all questions will be added to if no category is selected.', 'ask-me-anything' ),
					'type'    => 'select',
					'options' => ask_me_anything_get_categories(),
					'std'     => ''
				),
				'statuses'              => array(
					'id'   => 'statuses',
					'name' => __( 'Statuses', 'ask-me-anything' ),
					'desc' => __( 'Insert the list of statuses you want made available for questions. Put each status on a new line. Your default status should be the first entry. That\'s the one that will be auto assigned to new questions.', 'ask-me-anything', 'ask-me-anything' ),
					'type' => 'textarea',
					'std'  => "Pending\nIn Progress\nCompleted"
				),
			),
			'fields'        => array(
				'require_name'          => array(
					'id'   => 'require_name',
					'name' => __( 'Require Name', 'ask-me-anything' ),
					'desc' => __( 'Check this on to require that a name be entered when submitting a question.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => false
				),
				'require_email'         => array(
					'id'   => 'require_email',
					'name' => __( 'Require Email', 'ask-me-anything' ),
					'desc' => __( 'Check this on to require that an email address be entered when submitting a question.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => false
				),
				'allow_category_select' => array(
					'id'   => 'allow_category_select',
					'name' => __( 'Allow Category Selection', 'ask-me-anything' ),
					'desc' => __( 'If checked, users will be able to choose a category when submitting their question. If unchecked, categories are only viewable by you.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => '1'
				),
				'question_field_name'   => array(
					'id'   => 'question_field_name',
					'name' => __( 'Question Field Name', 'ask-me-anything' ),
					'desc' => __( 'By default, the main textarea box is called "Question". But you can change this to something else if you\'re using it for something other than questions (i.e. "Request", "Message", etc.).', 'ask-me-anything' ),
					'type' => 'text',
					'std'  => __( 'Question', 'ask-me-anything' )
				)
			),
			'labels'        => array(
				'button_text'          => array(
					'id'   => 'button_text',
					'name' => __( 'Button Text', 'ask-me-anything' ),
					'desc' => __( 'Text displayed on the floating button that opens the form.', 'ask-me-anything' ),
					'type' => 'text',
					'std'  => __( 'Ask Me a Question', 'ask-me-anything' )
				),
				'form_title'           => array(
					'id'   => 'form_title',
					'name' => __( 'Form Title', 'ask-me-anything' ),
					'desc' => __( 'Title text displayed above the submission form.', 'ask-me-anything' ),
					'type' => 'text',
					'std'  => __( 'Ask Me Anything', 'ask-me-anything' )
				),
				'form_desc'            => array(
					'id'   => 'form_desc',
					'name' => __( 'Form Description', 'ask-me-anything' ),
					'desc' => __( 'Appears above the form submission fields.', 'ask-me-anything' ),
					'type' => 'tinymce',
					'std'  => ''
				),
				'no_questions_message' => array(
					'id'   => 'no_questions_message',
					'name' => __( 'No Questions Message', 'ask-me-anything' ),
					'desc' => __( 'This message appears in the modal if questions are set to display on the front-end, but there are no questions to load.', 'ask-me-anything' ),
					'type' => 'textarea',
					'std'  => __( 'No questions yet!', 'ask-me-anything' )
				),
				'form_success'         => array(
					'id'   => 'form_success',
					'name' => __( 'Success Message', 'ask-me-anything' ),
					'desc' => __( 'This message will appear to the user after successfully submitting a question.', 'ask-me-anything' ),
					'type' => 'textarea',
					'std'  => __( 'Success! Your question has been submitted. I\'ll answer it as soon as I can!', 'ask-me-anything' )
				)
			),
			'notifications' => array(
				'admin_notifications'  => array(
					'id'   => 'admin_notifications',
					'name' => __( 'Notify Admin', 'ask-me-anything' ),
					'desc' => __( 'Check this to email the site administrator whenever a new question is submitted.', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
				'admin_email'          => array(
					'id'   => 'admin_email',
					'name' => __( 'Notify Email Addresses', 'ask-me-anything' ),
					'desc' => __( 'Enter a comma-separated list of emails. These are the email addresses we\'ll notify when a new question is submitted.', 'ask-me-anything' ),
					'type' => 'text',
					'std'  => get_option( 'admin_email' )
				),
				'notification_header'  => array(
					'id'   => 'notification_header',
					'name' => __( 'Comment Notifications', 'ask-me-anything' ),
					'desc' => __( 'The below settings apply to comment notification emails. These get sent out when someone subscribes to a question and a new comment is posted.', 'ask-me-anything' ),
					'type' => 'header'
				),
				'notification_subject' => array(
					'id'   => 'notification_subject',
					'name' => __( 'Subject', 'ask-me-anything' ),
					'desc' => sprintf( __( 'Subject for the notification email. You can use the placeholder %s for the question\'s subject.', 'ask-me-anything' ), '<code>[subject]</code>' ),
					'type' => 'text',
					'std'  => sprintf( __( 'New comment on question "%s"', 'ask-me-anything' ), '[subject]' )
				),
				'notification_message' => array(
					'id'   => 'notification_message',
					'name' => __( 'Message', 'ask-me-anything' ),
					'desc' => sprintf( __( 'Message for the notification email. No HTML allowed. You can use the following placeholders: <br>%1$s - Question subject <br>%2$s - Question message <br>%3$s - Question link', 'ask-me-anything' ), '<code>[subject]</code>', '<code>[message]</code>', '<code>[link]</code>' ),
					'type' => 'textarea',
					'std'  => sprintf( __( 'A new comment has been posted on "%1$s":' . "\n\n" . '%2$s' . "\n\n" . 'Link: %3$s', 'ask-me-anything' ), '[subject]', '[message]', '[link]' )
				)
			)
		) ),
		/* Styles */
		'styles'    => apply_filters( 'ask-me-anything/settings/styles', array(
			'main' => array(
				'disable_styles'       => array(
					'id'   => 'disable_styles',
					'name' => __( 'Disable Styles', 'ask-me-anything' ),
					'desc' => __( 'Check this to disable the Ask Me Anything stylesheet from being added to your site.', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
				'disable_font_awesome' => array(
					'id'   => 'disable_font_awesome',
					'name' => __( 'Disable Font Awesome', 'ask-me-anything' ),
					'desc' => __( 'Check this to stop Ask Me Anything from loading Font Awesome. You only want to check this if Font Awesome is already being loaded by your theme or another plugin.', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
				'button_bg_colour'     => array(
					'id'   => 'button_bg_colour',
					'name' => __( 'Button BG Colour', 'ask-me-anything' ),
					'desc' => __( 'Background colour for all Ask Me Anything buttons.', 'ask-me-anything' ),
					'type' => 'color',
					'std'  => '#e14d43'
				),
				'button_text_colour'   => array(
					'id'   => 'button_text_colour',
					'name' => __( 'Button Text Colour', 'ask-me-anything' ),
					'desc' => __( 'Ttext colour for all Ask Me Anything buttons.', 'ask-me-anything' ),
					'type' => 'color',
					'std'  => '#ffffff'
				)
			)
		) ),
		/* Misc Settings */
		'misc'      => apply_filters( 'ask-me-anything/settings/misc', array(
			'main' => array(
				'delete_on_uninstall' => array(
					'id'   => 'delete_on_uninstall',
					'name' => __( 'Remove Data on Uninstall', 'ask-me-anything' ),
					'desc' => __( 'Check this box if you would like Ask Me Anything to completely erase all of its data when the plugin is deleted. If checked and the plugin is uninstalled, the data is gone forever!', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
			),
		) ),
		/* Licenses */
		'licenses'  => apply_filters( 'ask-me-anything/settings/licenses', array() )
	);

	return apply_filters( 'ask-me-anything/settings/registered-settings', $ask_me_anything_settings );

}

/**
 * Status Colours
 *
 * Adds extra settings to the 'styles' tab containing colour pickers for customizing the
 * status buttons.
 *
 * @param array $settings Style settings
 *
 * @since 1.0.0
 * @return array
 */
function ask_me_anything_status_colours( $settings = array() ) {
	$statuses = ask_me_anything_get_statuses();

	if ( ! is_array( $statuses ) ) {
		return $settings;
	}

	$defaults = array(
		'ama_pending'    => '#0096dd',
		'ama_inprogress' => '#EFA652',
		'ama_completed'  => '#3BB14D'
	);

	$settings['statuses']['status_colours_header'] = array(
		'id'   => 'status_colours_header',
		'name' => __( 'Status Labels', 'ask-me-anything' ),
		'desc' => __( 'Customize the colours of the status labels. These settings will be applied to the front-end and the Questions > All Questions admin page.', 'ask-me-anything' ),
		'type' => 'header'
	);

	foreach ( $statuses as $key => $name ) {
		$settings['statuses'][ $key . '_bg_colour' ] = array(
			'id'   => $key . '_bg_colour',
			'name' => sprintf( __( '%s BG Colour', 'ask-me-anything' ), esc_html( $name ) ),
			'type' => 'color',
			'std'  => ( array_key_exists( $key, $defaults ) ) ? $defaults[ $key ] : $defaults['ama_pending']
		);

		$settings['statuses'][ $key . '_text_colour' ] = array(
			'id'   => $key . '_text_colour',
			'name' => sprintf( __( '%s Text Colour', 'ask-me-anything' ), esc_html( $name ) ),
			'type' => 'color',
			'std'  => '#ffffff'
		);
	}

	return $settings;
}

add_filter( 'ask-me-anything/settings/styles', 'ask_me_anything_status_colours' );

/**
 * Sanitize Settings
 *
 * Adds a settings error for the updated message.
 *
 * @param array  $input                   The value inputted in the field
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything options
 *
 * @since 1.0.0
 * @return array New, sanitized settings.
 */
function ask_me_anything_settings_sanitize( $input = array() ) {

	global $ask_me_anything_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = ask_me_anything_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'book';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();
	$input = apply_filters( 'ask-me-anything/settings/sanitize/' . $tab . '/' . $section, $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {
		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $section ][ $key ]['type'] ) ? $settings[ $tab ][ $section ][ $key ]['type'] : false;
		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'ask-me-anything/settings/sanitize/' . $type, $value, $key );
		}
		// General filter
		$input[ $key ] = apply_filters( 'ask-me-anything/settings/sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();
	$found_settings   = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {
			if ( empty( $input[ $key ] ) ) {
				unset( $ask_me_anything_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $ask_me_anything_options, $input );

	add_settings_error( 'ask-me-anything-notices', '', __( 'Settings updated.', 'ask-me-anything' ), 'updated' );

	return $output;

}

/**
 * Retrieve settings tabs
 *
 * @since 1.0.0
 * @return array $tabs
 */
function ask_me_anything_get_settings_tabs() {
	$tabs              = array();
	$tabs['questions'] = __( 'Questions', 'ask-me-anything' );
	$tabs['styles']    = __( 'Styles', 'ask-me-anything' );
	$tabs['misc']      = __( 'Misc', 'ask-me-anything' );
	$tabs['licenses']  = __( 'Licenses', 'ask-me-anything' );

	return apply_filters( 'ask-me-anything/settings/tabs', $tabs );
}


/**
 * Retrieve settings tabs
 *
 * @since 1.0.0
 * @return array $section
 */
function ask_me_anything_get_settings_tab_sections( $tab = false ) {
	$tabs     = false;
	$sections = ask_me_anything_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  1.0.0
 * @return array Array of tabs and sections
 */
function ask_me_anything_get_registered_settings_sections() {
	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'questions' => apply_filters( 'ask-me-anything/settings/sections/questions', array(
			'main'          => __( 'Questions', 'ask-me-anything' ),
			'fields'        => __( 'Fields', 'ask-me-anything' ),
			'labels'        => __( 'Labels', 'ask-me-anything' ),
			'notifications' => __( 'Notifications', 'ask-me-anything' )
		) ),
		'styles'    => apply_filters( 'ask-me-anything/settings/sections/styles', array(
			'main'     => __( 'Styles', 'ask-me-anything' ),
			'statuses' => __( 'Status Colours', 'ask-me-anything' )
		) ),
		'misc'      => apply_filters( 'ask-me-anything/settings/sections/misc', array(
			'main' => __( 'Misc', 'ask-me-anything' )
		) ),
		'licenses'  => apply_filters( 'ask-me-anything/settings/sections/licenses', array(
			'main' => __( 'Licenses', 'ask-me-anything' ),
		) )
	);

	$sections = apply_filters( 'ask-me-anything/settings/sections', $sections );

	return $sections;
}

/**
 * Sanitizes a string key for Ask Me Anything Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are
 * allowed
 *
 * @param  string $key String key
 *
 * @since 1.0.0
 * @return string Sanitized key
 */
function ask_me_anything_sanitize_key( $key ) {
	$raw_key = $key;
	$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	return apply_filters( 'ask-me-anything/sanitize-key', $key, $raw_key );
}

/**
 * Sanitize: Colour Field
 *
 * @param string $value
 * @param string $key
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_sanitize_color_field( $value, $key ) {
	if ( '' === $value ) {
		return '';
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $value ) ) {
		return $value;
	}
}

add_filter( 'ask-me-anything/settings/sanitize/color', 'ask_me_anything_sanitize_color_field', 10, 2 );


/*
 * Callbacks
 */

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @param array $args Arguments passed by the setting
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'ask-me-anything' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything settings
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_text_callback( $args ) {
	global $ask_me_anything_options;

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="ask_me_anything_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$type = array_key_exists( 'input-type', $args ) ? $args['input-type'] : 'text';

	$readonly = ( array_key_exists( 'readonly', $args ) && $args['readonly'] ) === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	?>
	<input type="<?php echo esc_attr( $type ); ?>" class="<?php echo sanitize_html_class( $size ); ?>-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo $readonly; ?>>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Header Callback
 *
 * Simply renders a title and description.
 *
 * @param array $args Arguments passed by the setting
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_header_callback( $args ) {
	if ( array_key_exists( 'desc', $args ) ) {
		echo '<div class="desc">' . wp_kses_post( $args['desc'] ) . '</div>';
	}
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything settings
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_textarea_callback( $args ) {
	global $ask_me_anything_options;

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	?>
	<textarea class="large-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" name="ask_me_anything_settings[<?php echo esc_attr( $args['id'] ); ?>]" rows="10" cols="50"><?php echo esc_textarea( $value ); ?></textarea>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the EDD Options
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_color_callback( $args ) {
	global $ask_me_anything_options;

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';
	?>
	<input type="text" class="ama-color-picker" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>" name="ask_me_anything_settings[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>">
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * License Key Callback
 *
 * Renders license key fields.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything settings
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_license_key_callback( $args ) {
	global $ask_me_anything_options;

	$messages = array();
	$class    = '';
	$license  = get_option( $args['options']['is_valid_license_option'] );

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( ! empty( $license ) && is_object( $license ) ) {

		if ( false === $license->success ) {

			switch ( $license->error ) {

				case 'expired' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'ask-me-anything' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
						'https://shop.nosegraze.com/checkout/?edd_license_key=' . urlencode( $value ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'missing' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'ask-me-anything' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'invalid' :
				case 'site_inactive' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'ask-me-anything' ),
						$args['name'],
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'item_name_mismatch' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'This is not a %s.', 'ask-me-anything' ),
						$args['name']
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'no_activations_left' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your license key has reached its activation limit. <a href="%s" target="_blank" title="View upgrades">View possible upgrades.</a>', 'ask-me-anything' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=no_activations_left'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

			}

		} else {

			$class      = 'valid';
			$now        = current_time( 'timestamp' );
			$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

			if ( 'lifetime' === $license->expires ) {

				$messages[]     = __( 'License key never expires.', 'ask-me-anything' );
				$license_status = 'license-lifetime-notice';

			} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

				$messages[] = sprintf(
					__( 'Your license key is about to expire! It expires on %1$s. <a href="%2$s" target="_blank" title="Renew license key">Renew your license key</a> to continue getting updates and support.', 'ask-me-anything' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
					'https://shop.nosegraze.com/checkout/?edd_license_key=' . urlencode( $value ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
				);

				$license_status = 'license-expires-soon-notice';

			} else {

				$messages[] = sprintf(
					__( 'Your license key expires on %s.', 'ask-me-anything' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
				);

				$license_status = 'license-expiration-date-notice';

			}

		}

	} else {
		$license_status = null;
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

	$wrapper_class = isset( $license_status ) ? $license_status : 'license-null';
	?>
	<div class="<?php echo sanitize_html_class( $wrapper_class ); ?>">
		<input type="text" class="<?php echo sanitize_html_class( $size ); ?>-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" name="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>">
		<?php

		// License key is valid, so let's show a deactivate button.
		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			?>
			<input type="submit" class="button-secondary" name="<?php echo esc_attr( $args['id'] ); ?>_deactivate" value="<?php _e( 'Deactivate License', 'ask-me-anything' ); ?>">
			<?php
		}

		?>
		<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php

		if ( ! empty( $messages ) && is_array( $messages ) ) {
			foreach ( $messages as $message ) {
				?>
				<div class="ask-me-anything-license-data ask-me-anything-license-<?php echo sanitize_html_class( $class ); ?> desc">
					<p><?php echo $message; ?></p>
				</div>
				<?php
			}
		}

		wp_nonce_field( ask_me_anything_sanitize_key( $args['id'] ) . '-nonce', ask_me_anything_sanitize_key( $args['id'] ) . '-nonce' );
		?>
	</div>
	<?php
}

/**
 * Checkbox Callback
 *
 * Renders a checkbox field.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything settings
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_checkbox_callback( $args ) {
	global $ask_me_anything_options;

	$checked = isset( $ask_me_anything_options[ $args['id'] ] ) ? checked( 1, $ask_me_anything_options[ $args['id'] ], false ) : '';
	?>
	<input type="checkbox" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" name="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" value="1" <?php echo $checked; ?>>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything Options
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_select_callback( $args ) {
	global $ask_me_anything_options;

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="ask-me-anything-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="ask_me_anything_settings[' . ask_me_anything_sanitize_key( $args['id'] ) . ']" name="ask_me_anything_settings[' . esc_attr( $args['id'] ) . ']" ' . $chosen . 'data-placeholder="' . esc_html( $placeholder ) . '">';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="ask_me_anything_settings[' . ask_me_anything_sanitize_key( $args['id'] ) . ']" class="desc"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
}

/**
 * TinyMCE Callback
 *
 * Renders a rich text editor.
 *
 * @param array  $args                    Arguments passed by the setting
 *
 * @global array $ask_me_anything_options Array of all the Ask Me Anything Options
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_tinymce_callback( $args ) {
	global $ask_me_anything_options;

	if ( isset( $ask_me_anything_options[ $args['id'] ] ) ) {
		$value = $ask_me_anything_options[ $args['id'] ];

		if ( empty( $args['allow_blank'] ) && empty( $value ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	wp_editor( stripslashes( $value ), 'ask_me_anything_settings' . esc_attr( $args['id'] ), array(
		'textarea_name' => 'ask_me_anything_settings[' . esc_attr( $args['id'] ) . ']',
		'textarea_rows' => absint( $rows )
	) );
	?>
	<br>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc">
		<?php echo wp_kses_post( $args['desc'] ); ?>
	</label>
	<?php
}