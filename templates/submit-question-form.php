<?php
/**
 * Submit Question Form
 *
 * Displays the form for submitting a question.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Labels
$title_text  = ask_me_anything_get_option( 'form_title', __( 'Ask Me Anything', 'ask-me-anything' ) );
$description = ask_me_anything_get_option( 'form_desc' );

// Requirements
$require_name  = ask_me_anything_get_option( 'require_name', false );
$require_email = ask_me_anything_get_option( 'require_email', false );
?>

<div class="ask-me-anything-submit-question">
	<?php if ( $title_text ) : ?>
		<h3 class="ask-me-anything-submit-title"><?php echo esc_html( $title_text ); ?></h3>
	<?php endif; ?>

	<?php if ( $description ) : ?>
		<div class="ask-me-anything-submit-description">
			<?php echo wpautop( wp_kses_post( $description ) ); ?>
		</div>
	<?php endif; ?>

	<form method="POST">
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-name"><?php _e( 'Your Name', 'ask-me-anything' ); ?><?php echo $require_name ? '<span class="ask-me-anything-required">*</span>' : ''; ?></label>
			<input type="text" class="ask-me-anything-name" name="ask-me-anything-name"<?php echo $require_name ? ' required' : ''; ?>>
		</div>
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-email"><?php _e( 'Email Address', 'ask-me-anything' ); ?><?php echo $require_email ? '<span class="ask-me-anything-required">*</span>' : ''; ?></label>
			<input type="email" class="ask-me-anything-email" name="ask-me-anything-email"<?php echo $require_email ? ' required' : ''; ?>>
		</div>
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-question"><?php echo esc_html( ask_me_anything_get_option( 'question_field_name', __( 'Question', 'ask-me-anything' ) ) ); ?></label>
			<textarea class="ask-me-anything-question" name="ask-me-anything-question"></textarea>
		</div>
	</form>
</div>
