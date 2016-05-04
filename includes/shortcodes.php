<?php
/**
 * Shortcodes
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Button Shortcode
 *
 * Displays an AMA trigger button for opening the modal.
 *
 * @param array  $atts    Shortcode attributes
 * @param string $content Content
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_button_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'text' => ask_me_anything_get_option( 'button_text', __( 'Ask Me a Question', 'ask-me-anything' ) )
	), $atts, 'ama-button' );

	ob_start();
	?>
	<button class="ask-me-anything-button ask-me-anything-trigger-button" data-target="#ask-me-anything"><?php echo $atts['text']; ?></button>
	<?php

	// If the modal is not set to auto display, we need to include that too!
	$display = ask_me_anything_get_option( 'display_position', 'bottom-right' );

	// Display is turned off - bail.
	if ( empty( $display ) || $display == 'none' ) {
		ask_me_anything_get_template_part( 'main', 'template' );
	}

	return ob_get_clean();
}

add_shortcode( 'ama-button', 'ask_me_anything_button_shortcode' );