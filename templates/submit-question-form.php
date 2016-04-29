<?php
/**
 * Submit Question Form (Underscores JS Template)
 *
 * Displays the form for submitting a question.
 *
 * The following JS variables are available:
 *      data.form_title_text -- "Form Title", as per settings panel.
 *      data.form_description -- "Form Description", as per settings panel.
 *      data.form_require_name (bool) -- Whether or not the name field is required.
 *      data.form_require_email (bool) -- Whether or not the email field is required.
 *      data.form_question_field_name -- The label for the main question textarea box.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
?>

<script id="tmpl-ama-submit-form" type="text/html">
	<# if ( data.form_title_text != '' ) { #>
		<h3 class="ask-me-anything-submit-title">{{ data.form_title_text }}</h3>
	<# } #>

	<# if ( data.form_description != '' ) { #>
		<div class="ask-me-anything-submit-description">
			<?php echo wpautop( wp_kses_post( $description ) ); ?>
		</div>
	<# } #>

	<form method="POST">
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-name"><?php _e( 'Your Name', 'ask-me-anything' ); ?><# if (data.form_require_name) { #><span class="ask-me-anything-required">*</span><# } #></label>
			<input type="text" class="ask-me-anything-name" name="ask-me-anything-name"<# if (data.form_require_name) { #> required<# } #>>
		</div>
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-email"><?php _e( 'Email Address', 'ask-me-anything' ); ?><# if (data.form_require_email) { #><span class="ask-me-anything-required">*</span><# } #></label>
			<input type="email" class="ask-me-anything-email" name="ask-me-anything-email"<# if (data.form_require_email) { #> required<# } #>>
		</div>
		<div class="ask-me-anything-field">
			<label for="ask-me-anything-question">{{ data.form_question_field_name }}</label>
			<textarea class="ask-me-anything-question" name="ask-me-anything-question"></textarea>
		</div>
	</form>
</script>
