<?php
/**
 * Template for displaying a single question as well as any comments.
 * (Underscores JS Template)
 *
 * Be sure to leave all class names and IDs in tact in order to ensure the plugin can function as normal.
 * You can move them around, but don't delete or modify the class names or IDs - particularly with the
 * comment submission form.
 *
 * The following variables are available:
 *
 *  + data.question_id - ID number of the question.
 *  + data.question_title - Title (subject) of the question.
 *  + data.question_url - URL to the question.
 *  + data.question_status - Status (name) of the question.
 *  + data.question_status_class - Class for the status (to be used in a 'class' attribute).
 *  + data.question_content - Actual content of the question.
 *  + data.question_submitter - Name of the person who submitted the content. Blank if anonymous.
 *  + data.number_comments - Number of comments on this question.
 *  + data.comments_title - Text for the comments title, based on how many comments the question has. Will be
 *                          "Leave a Comment" for zero comments, "One Comment" for one comment, or "4 Comments"
 *                          for multiple comments.
 *  + data.number_up - Number of up votes.
 *  + data.number_down - Number of down votes.
 *  + data.question_edit_link - Link to the "Edit Question" page in the admin panl.
 *                              This is blank if the current user doesn't have permission to edit the question.
 *  + data.comment_author - Name of the current commenter (or logged in user).
 *  + data.comment_author_email - Email address of the current commenter (or logged in user).
 *  + data.comment_author_url - URL of the current commenter (or logged in user).
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$voting = ask_me_anything_get_option( 'voting', 'all' ); ?>

<script id="tmpl-ama-single-question" type="text/html">

	<a href="#" class="ama-load-question-form"><?php _e( '&laquo; Back to Form', 'ask-me-anything' ); ?></a>

	<div id="question-{{ data.question_id }}" class="ama-single-question-wrap {{ data.question_status_class }}" data-question-id="{{ data.question_id }}">

		<h2>{{{ data.question_title }}}</h2>

		{{{ data.question_content }}}

		<span class="ama-question-submitter">
			<# if (data.question_submitter) { #>
				- {{ data.question_submitter }}
			<# } else { #>
				- <?php _e('Anonymous', 'ask-me-anything'); ?>
			<# } #>
		</span>

		<div class="ama-question-actions">
			<# if (data.question_edit_link) { #>
				<a href="{{{ data.question_edit_link }}}" target="_blank" class="ama-question-edit-link"><?php _e('Edit', 'ask-me-anything'); ?></a>
			<# } #>

			<?php if ( $voting != 'none' ) : ?>
				<span class="ama-question-voting">
					<?php if ( $voting == 'all' || $voting == 'up' ) : ?>
						<span class="ama-up-vote">
							<i class="fa fa-thumbs-up"></i>
							<span class="ama-vote-number">{{ data.number_up }}</span>
						</span>
					<?php endif; ?>
					<?php if ( $voting == 'all' || $voting == 'down' ) : ?>
						<span class="ama-down-vote">
							<i class="fa fa-thumbs-down"></i>
							<span class="ama-vote-number">{{ data.number_down }}</span>
						</span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</div>

	</div>

	<?php if ( ask_me_anything_get_option( 'comments_on_questions' ) ) : ?>
		<div class="ama-single-question-comments">

			<h3>{{ data.comments_title }}</h3>

			<div class="ama-comments-list">
				<?php
				/*
				 * Comments
				 *
				 * List of individual comments is inserted here.
				 * @see comments.php
				 */
				?>
			</div>

			<form id="ama-submit-comment-form" method="POST">
				<div class="ama-comment-name-field-wrap">
					<label for="ama-comment-name-field" class="screen-reader-text"><?php _e( 'Your Name', 'ask-me-anything' ); ?></label>
					<input type="text" id="ama-comment-name-field" name="ama_comment_name" placeholder="<?php esc_attr_e( 'Your Name (required)', 'ask-me-anything' ); ?>" value="{{ data.comment_author }}" required>
				</div>
				<div class="ama-comment-email-field-wrap">
					<label for="ama-comment-email-field" class="screen-reader-text"><?php _e( 'Your Email Address', 'ask-me-anything' ); ?></label>
					<input type="email" id="ama-comment-email-field" name="ama_comment_email" placeholder="<?php esc_attr_e( 'Your Email (required)', 'ask-me-anything' ); ?>" value="{{ data.comment_author_email }}" required>
				</div>
				<div class="ama-comment-message-field-wrap">
					<label for="ama-comment-message-field" class="screen-reader-text"><?php _e( 'Comment', 'ask-me-anything' ); ?></label>
					<textarea id="ama-comment-message-field" name="ama_comment" placeholder="<?php esc_attr_e( 'Enter your comment', 'ask-me-anything' ); ?>" required></textarea>
				</div>
				<div class="ama-comment-notify-field-wrap">
					<label for="ama-comment-notify-field">
						<input type="checkbox" id="ama-comment-notify-field" name="ama_comment_notify" value="1" checked>
						<?php _e( 'Notify me of new comments', 'ask-me-anything' ); ?>
					</label>
				</div>
				<?php if ( current_user_can( 'edit_questions' ) ) : ?>
					<?php $ama_statuses = ask_me_anything_get_statuses(); ?>
					<div class="ama-comment-status-field-wrap">
						<label for="ama-comment-status-field"><?php _e('Update question status', 'ask-me-anything'); ?></label>
						<select id="ama-comment-status-field" name="ama-comment-status-field">
							<option value="" selected><?php _e( 'No change', 'ask-me-anything' ); ?></option>
							<?php foreach ( $ama_statuses as $key => $name ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo $name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<button type="submit" class="ask-me-anything-button ama-submit-comment-button"><?php _e( 'Submit Comment', 'ask-me-anything' ); ?></button>
			</form>
		</div>
	<?php endif; ?>
	
	<?php do_action( 'ask-me-anything/single-question/after-comments' ); ?>

</script>
