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
		<h2 class="ask-me-anything-submit-title">{{ data.form_title_text }}</h2>
	<# } #>

	<# if ( data.form_description != '' ) { #>
		<div class="ask-me-anything-submit-description">
			{{{ data.form_description }}}
		</div>
	<# } #>

	<# if ( data.message != '' ) { #>
		{{{ data.message }}}
	<# } #>

	<form class="ask-me-anything-submit-question-form" method="POST">

		<div class="ask-me-anything-field">
			<label for="ask-me-anything-name"><?php echo apply_filters( 'ask-me-anything/submit-form/label/name', __( 'Your Name', 'ask-me-anything' ) ); ?><# if (data.form_require_name) { #><span class="ask-me-anything-required">*</span><# } #></label>
			<input type="text" class="ask-me-anything-name" name="ask-me-anything-name"<# if (data.form_require_name) { #> required<# } #>>
		</div>

		<?php do_action( 'ask-me-anything/submit-form/after-name-field' ); ?>

		<div class="ask-me-anything-field">
			<label for="ask-me-anything-email"><?php echo apply_filters( 'ask-me-anything/submit-form/label/email', __( 'Email Address', 'ask-me-anything' ) ); ?><# if (data.form_require_email) { #><span class="ask-me-anything-required">*</span><# } #></label>
			<input type="email" class="ask-me-anything-email" name="ask-me-anything-email"<# if (data.form_require_email) { #> required<# } #>>
		</div>

		<?php do_action( 'ask-me-anything/submit-form/after-email-field' ); ?>

		<?php
		/*
		 * Select a Category
		 * If there's more than one category available, we show a dropdown of choices.
		 * Otherwise, we insert a hidden field with the value of the default category.
		 */
		$categories = ask_me_anything_get_categories_dropdown();
		if ( $categories && ask_me_anything_get_option( 'allow_category_select' ) ) : ?>
			<div class="ask-me-anything-field">
				<label for="ask-me-anything-category"><?php echo apply_filters( 'ask-me-anything/submit-form/label/category', __( 'Category', 'ask-me-anything' ) ); ?></label>
				<select name="ask-me-anything-category">
					<?php echo $categories; ?>
				</select>
			</div>
		<?php else : ?>
			<input type="hidden" name="ask-me-anything-category" value="<?php echo esc_attr( absint( ask_me_anything_get_option( 'default_category' ) ) ); ?>">
		<?php endif; ?>

		<div class="ask-me-anything-field">
			<label for="ask-me-anything-subject"><?php echo apply_filters( 'ask-me-anything/submit-form/label/subject', __( 'Subject', 'ask-me-anything' ) ); ?><span class="ask-me-anything-required">*</span></label>
			<input type="text" class="ask-me-anything-subject" name="ask-me-anything-subject" required>
		</div>

		<?php do_action( 'ask-me-anything/submit-form/after-subject-field' ); ?>

		<div class="ask-me-anything-field">
			<label for="ask-me-anything-question">{{ data.form_question_field_name }}<span class="ask-me-anything-required">*</span></label>
			<textarea class="ask-me-anything-question" name="ask-me-anything-question" required></textarea>
		</div>

		<?php do_action( 'ask-me-anything/submit-form/after-question-field' ); ?>

		<?php if ( ask_me_anything_get_option( 'comments_on_questions', true ) ) : ?>
			<div class="ask-me-anything-field">
				<label for="ask-me-anything-notify">
					<input type="checkbox" class="ask-me-anything-notify" name="ask-me-anything-notify" value="1" checked>
					<?php _e( 'Notify me of new responses to my question', 'ask-me-anything' ); ?>
				</label>
			</div>
		<?php endif; ?>

		<?php do_action( 'ask-me-anything/submit-form/after-notify-field' ); ?>

		<button type="submit" class="ask-me-anything-button ask-me-anything-submit-question-button"><?php echo apply_filters( 'ask-me-anything/submit-form/label/submit', __( 'Submit', 'ask-me-anything' ) ); ?></button>

	</form>
</script>
