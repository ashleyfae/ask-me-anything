<?php
/**
 * Template for displaying a single question as well as any comments.
 * (Underscores JS Template)
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$voting = ask_me_anything_get_option( 'voting', 'all' ); ?>

<script id="tmpl-ama-single-question" type="text/html">

	<button class="ama-load-question-form"><?php _e( 'Back to Form', 'ask-me-anything' ); ?></button>

	<div id="question-{{ data.question_id }}" class="ama-single-question-wrap">

		<h2>{{ data.question_title }}</h2>

		{{{ data.question_content }}}

		<# if (data.question_submitter) { #>
			<span class="ama-question-submitter">{{ data.question_submitter }}</span>
		<# } #>

		<div class="ama-question-actions">
			<# if (data.question_edit_link) { #>
				<a href="{{{ data.question_edit_link }}}" target="_blank" class="ama-question-edit-link"><?php _e('Edit', 'ask-me-anything'); ?></a>
			<# } #>

			<?php if ( $voting != 'none' ) : ?>
				<span class="ama-question-voting">
					<?php if ( $voting == 'all' || $voting == 'up' ) : ?>
						<span class="ama-up-vote">
							<i class="fa fa-thumbs-up"></i> {{ data.number_up }}
						</span>
					<?php endif; ?>
					<?php if ( $voting == 'all' || $voting == 'down' ) : ?>
						<span class="ama-down-vote">
							<i class="fa fa-thumbs-down"></i> {{ data.number_down }}
						</span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</div>

	</div>

	<?php if ( ask_me_anything_get_option( 'comments_on_questions', true ) ) : ?>
		<div class="ama-single-question-comments">
			<form id="ama-submit-comment-form" method="POST">
				<div class="ama-comment-name-field-wrap">
					<label for="ama-comment-name-field"><?php _e( 'Your Name', 'ask-me-anything' ); ?></label>
					<input type="text" name="ama_comment_name" placeholder="<?php esc_attr_e( 'Your Name', 'ask-me-anything' ); ?>">
				</div>
				<div class="ama-comment-email-field-wrap">
					<label for="ama-comment-email-field"><?php _e( 'Your Email Address', 'ask-me-anything' ); ?></label>
					<input type="email" name="ama_comment_email" placeholder="<?php esc_attr_e( 'Your Email', 'ask-me-anything' ); ?>">
				</div>
				<div class="ama-comment-message-field-wrap">
					<label for="ama-comment-message-field"><?php _e( 'Your Email Address', 'ask-me-anything' ); ?></label>
					<textarea name="ama_comment" placeholder="<?php esc_attr_e( 'Enter your comment', 'ask-me-anything' ); ?>"></textarea>
				</div>
				<div class="ama-comment-notify-field-wrap">
					<label for="ama-comment-notify-field">
						<input type="checkbox" name="ama_comment_notify" checked>
						<?php _e( 'Notify me of new comments', 'ask-me-anything' ); ?>
					</label>
				</div>
				<button type="submit" class="ama-submit-comment-button"><?php _e( 'Submit Comment', 'ask-me-anything' ); ?></button>
			</form>
		</div>
	<?php endif; ?>

</script>
