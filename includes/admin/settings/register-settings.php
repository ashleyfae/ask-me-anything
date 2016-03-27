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

	if ( empty( $settings ) ) {
		// Update old settings with new single option
		$general_settings  = is_array( get_option( 'ask_me_anything_settings_general' ) ) ? get_option( 'ask_me_anything_settings_general' ) : array();
		$question_settings = is_array( get_option( 'ask_me_anything_settings_questions' ) ) ? get_option( 'ask_me_anything_settings_questions' ) : array();
		$styles_settings   = is_array( get_option( 'ask_me_anything_settings_styles' ) ) ? get_option( 'ask_me_anything_settings_styles' ) : array();
		$tools_settings    = is_array( get_option( 'ask_me_anything_settings_tools' ) ) ? get_option( 'ask_me_anything_settings_tools' ) : array();
		$settings          = array_merge( $general_settings, $question_settings, $styles_settings, $tools_settings );

		update_option( 'ask_me_anything_settings', $settings );
	}

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
		/* General Settings */
		'general'   => apply_filters( 'ask-me-anything/settings/general', array(
			'main' => array(
				'license_key'         => array(
					'id'      => 'license_key',
					'name'    => __( 'License Key', 'ask-me-anything' ),
					'desc'    => __( 'Enter your license key to enable automatic updates. This can be found in your purchase receipt.', 'ask-me-anything' ),
					'type'    => 'license_key',
					'std'     => '',
					'options' => array(
						'item_name' => 'Ask Me Anything Plugin'
					)
				),
				'delete_on_uninstall' => array(
					'id'   => 'delete_on_uninstall',
					'name' => __( 'Remove Data on Uninstall', 'ask-me-anything' ),
					'desc' => __( 'Check this box if you would like Ask Me Anything to completely erase all of its data when the plugin is deleted. If checked and the plugin is uninstalled, the data is gone forever!', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
			),
		) ),
		/* Question Settings */
		'questions' => apply_filters( 'ask-me-anything/settings/questions', array(
			'main'          => array(
				'display_position' => array(
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
				'visibility'       => array(
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
				'show_questions'   => array(
					'id'   => 'show_questions',
					'name' => __( 'Show Questions on Front-End', 'ask-me-anything' ),
					'desc' => __( 'Check this to display the questions publicly. This allows your viewers to read through questions submitted by others.', 'ask-me-anything' ),
					'type' => 'checkbox',
					'std'  => '1'
				),
				'default_category' => array(
					'id'      => 'default_category',
					'name'    => __( 'Default Category', 'ask-me-anything' ),
					'desc'    => __( 'This is the category all questions will be added to if no category is selected.', 'ask-me-anything' ),
					'type'    => 'select',
					'options' => ask_me_anything_get_categories(),
					'std'     => ''
				),
				'statuses'         => array(
					'id'      => 'statuses',
					'name'    => __( 'Statuses', 'ask-me-anything' ),
					'desc'    => __( 'Insert the list of statuses you want made available for questions. Put each status on a new line. Your default status should be the first entry. That\'s the one that will be auto assigned to new questions.', 'ask-me-anything' ),
					'type'    => 'textarea',
					'options' => ask_me_anything_get_categories(),
					'std'     => "Pending\nIn Progress\nCompleted"
				),
			),
			'notifications' => array(
				'admin_notifications' => array(
					'id'   => 'admin_notifications',
					'name' => __( 'Notify Admin', 'ask-me-anything' ),
					'desc' => __( 'Check this to email the site administrator whenever a new question is submitted.', 'ask-me-anything' ),
					'type' => 'checkbox'
				),
				'admin_email'         => array(
					'id'   => 'admin_email',
					'name' => __( 'Notify Email Addresses', 'ask-me-anything' ),
					'desc' => __( 'Enter a comma-separated list of emails. These are the email addresses we\'ll notify when a new question is submitted' ),
					'type' => 'text',
					'std'  => get_option( 'admin_email' )
				)
			)
		) ),
		/* Styles */
		'styles'    => apply_filters( 'ask-me-anything/settings/styles', array(
			'main' => array(
				'disable_styles' => array(
					'id'   => 'disable_styles',
					'name' => __( 'Disable Styles', 'ask-me-anything' ),
					'desc' => __( 'Check this to disable the Ask Me Anything stylesheet from being added to your site.', 'ask-me-anything' ),
					'type' => 'checkbox'
				)
			)
		) ),
		/* Tools */
		/*'tools' => apply_filters( 'ask-me-anything/settings/tools', array(
			'main' => array()
		) )*/
	);

	return apply_filters( 'ask-me-anything/settings/registered-settings', $ask_me_anything_settings );

}

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
	$tabs['general']   = __( 'General', 'ask-me-anything' );
	$tabs['questions'] = __( 'Questions', 'ask-me-anything' );
	$tabs['styles']    = __( 'Styles', 'ask-me-anything' );

	//$tabs['tools'] = __( 'Tools', 'ask-me-anything' );

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
		'general'   => apply_filters( 'ask-me-anything/settings/sections/general', array(
			'main' => __( 'General', 'ask-me-anything' )
		) ),
		'questions' => apply_filters( 'ask-me-anything/settings/sections/questions', array(
			'main'          => __( 'Questions', 'ask-me-anything' ),
			'notifications' => __( 'Notifications', 'ask-me-anything' )
		) ),
		'styles'    => apply_filters( 'ask-me-anything/settings/sections/styles', array(
			'main' => __( 'Styles', 'ask-me-anything' )
		) ),
		'tools'     => apply_filters( 'ask-me-anything/settings/sections/tools', array(
			'main' => __( 'Tools', 'ask-me-anything' ),
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

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	?>
	<input type="text" class="<?php echo sanitize_html_class( $size ); ?>-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo $readonly; ?>>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
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
	<textarea class="large-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" name="ask_me_anything_settings[' . esc_attr( $args['id'] ) . ']" rows="10" cols="50"><?php echo esc_textarea( $value ); ?></textarea>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
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

	$status    = get_option( 'ask_me_anything_' . ask_me_anything_sanitize_key( $args['id'] ) . '_status' );
	$item_name = isset( $args['options']['item_name'] ) ? trim( $args['options']['item_name'] ) : '';

	if ( is_object( $status ) && $status->license == 'valid' ) {
		$label  = __( 'Deactivate', 'ask-me-anything' );
		$action = 'deactivate_license';
	} else {
		$label  = __( 'Activate', 'ask-me-anything' );
		$action = 'activate_license';
	}
	?>
	<input type="text" class="regular-text" id="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>">

	<button type="button" class="button button-secondary ama-validate-license" data-field-id="<?php echo esc_attr( 'ask_me_anything_settings[' . ask_me_anything_sanitize_key( $args['id'] ) . ']' ); ?>" data-option-name="<?php echo esc_attr( $args['id'] ); ?>" data-status-name="<?php echo esc_attr( 'ask_me_anything_' . ask_me_anything_sanitize_key( $args['id'] ) . '_status' ); ?>" data-product-name="<?php echo esc_attr( $item_name ); ?>" data-action="<?php echo esc_attr( $action ); ?>"><?php echo $label; ?></button>

	<div class="ask-me-anything-license-key-status">
		<?php
		if ( is_object( $status ) && $status->license == 'valid' ) {
			printf(
				__( 'Valid until %s', 'ask-me-anything' ),
				date_i18n( get_option( 'date_format' ), strtotime( $status->expires, current_time( 'timestamp' ) ) )
			);
		}
		?>
	</div>

	<?php
	if ( ! empty( $value ) && is_object( $status ) ) {
		if ( false === $status->success ) {

		}
	}
	?>
	<label for="ask_me_anything_settings[<?php echo ask_me_anything_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
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