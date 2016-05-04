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

/**
 * Inline UI
 *
 * Displays the Ask Me Anything box inline instead of in a popup.
 *
 * @param array  $atts    Shortcode attributes
 * @param string $content Content
 *
 * @since 1.0.0
 * @return string
 */
function ask_me_anything_inline_ui( $atts, $content = null ) {
	// Remove the modal because we can only have one per page, TYVM.
	remove_action( 'wp_footer', 'ask_me_anything_maybe_display', - 1 );

	$show_questions = ask_me_anything_get_option( 'show_questions' );
	$modal_class    = $show_questions ? ' ask-me-anything-has-questions' : '';

	ob_start();
	?>
	<div id="ask-me-anything" class="ask-me-anything-layout ask-me-anything-inline<?php echo esc_attr( $modal_class ); ?>">

		<div class="ask-me-anything-modal-inner">

			<?php
			/*
			 * List of questions on the right.
			 */
			if ( $show_questions ) : ?>
				<div class="ask-me-anything-questions-list">
					<?php
					/*
					 * @see questions.php
					 * That template gets pulled inside here and repeated for each individual question.
					 */
					?>
				</div>
			<?php endif;

			/*
			 * Submit a question form.
			 */
			?>
			<div class="ask-me-anything-submit-question">
				<?php
				/*
				 * @see submit-question-form.php
				 * That template gets pulled inside here automatically.
				 */
				?>
			</div>

		</div>

	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'ama', 'ask_me_anything_inline_ui' );