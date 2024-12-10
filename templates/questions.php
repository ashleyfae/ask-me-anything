<?php
/**
 * Template for a single question when displayed on the right-hand side.
 * (Underscores JS Template)
 *
 * The following variables are available:
 *
 *  + data.questions - Array of questions. We use _.each to loop through this.
 *                     The following variables are available for each question inside the _.each loop:
 *      + question.question_id - ID number of the question.
 *      + question.question_title - Title (subject) of the question.
 *      + question.question_url - URL to the question.
 *      + question.question_status - Name of the status
 *      + question.question_status_class - Class for the status (to be used in a 'class' attribute).
 *      + question.question_content - Actual content of the question.
 *      + question.question_submitter - Name of the person who submitted the content. Blank if anonymous.
 *      + question.number_comments - Number of comments on this question.
 *      + question.comments_title - Text for the comments title, based on how many comments the question has. Will be
 *                                  "Leave a Comment" for zero comments, "One Comment" for one comment, or "4 Comments"
 *                                  for multiple comments.
 *      + question.number_up - Number of up votes.
 *      + question.number_down - Number of down votes.
 *      + question.question_edit_link - Link to the "Edit Question" page in the admin panel.
 *                                      This is blank if the current user doesn't have permission to edit the question.
 *  + data.previouspage - Number for the previous page.
 *  + data.nextpage - Number for the next page.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$voting        = ask_me_anything_get_option( 'voting', 'all' );
$hide_statuses = ask_me_anything_get_option( 'hide_statuses', false );
?>

<script id="tmpl-ama-question" type="text/html">

	<# _.each( data.questions, function( question ) { #>

	<a href="{{ question.question_url }}" id="ama-question-item-{{ question.question_id }}" class="ama-question-item {{ question.question_status_class }}" data-postid="{{ question.question_id }}">
		<h3>
			{{{ question.question_title }}}
		</h3>

		<?php if ( $hide_statuses == false ) : ?>
			<span class="ama-question-status">{{ question.question_status }}</span>
		<?php endif; ?>
		
		<span class="ama-question-actions">
			<?php if ( ask_me_anything_comments_are_possible() ) : ?>
				<span class="ama-number-comments">
					<i class="fa fa-comments"></i> {{ question.number_comments }}
				</span>
			<?php endif; ?>

			<?php if ( $voting != 'none' ) : ?>
				<span class="ama-question-voting">
					<?php if ( $voting == 'all' || $voting == 'up' ) : ?>
						<span class="ama-up-vote">
							<i class="fa fa-thumbs-up"></i>
							<span class="ama-vote-number">{{ question.number_up }}</span>
						</span>
					<?php endif; ?>
					<?php if ( $voting == 'all' || $voting == 'down' ) : ?>
						<span class="ama-down-vote">
							<i class="fa fa-thumbs-down"></i>
							<span class="ama-vote-number">{{ question.number_down }}</span>
						</span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</span>
	</a>

	<# }); #>

	<div class="ama-pagination">
		<# if ( data.previouspage != 0 ) { #>
			<button type="button" class="ask-me-anything-button ama-previous-questions" data-page="{{ data.previouspage }}"><?php _e( 'Previous', 'ask-me-anything' ); ?></button>
		<# } #>

		<# if ( data.nextpage != 0 ) { #>
			<button type="button" class="ask-me-anything-button ama-next-questions" data-page="{{ data.nextpage }}"><?php _e( 'Next', 'ask-me-anything' ); ?></button>
		<# } #>
	</div>

</script>
