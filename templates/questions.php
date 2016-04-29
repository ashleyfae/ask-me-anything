<?php
/**
 * Template for a single question when displayed on the right-hand side.
 * (Underscores JS Template)
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$voting = ask_me_anything_get_option( 'voting', 'all' ); ?>

<script id="tmpl-ama-question" type="text/html">

	<# _.each( data.questions, function( question ) { #>

	<a href="{{ question.question_url }}" class="ama-question-item" data-postid="{{ question.question_id }}">
		<h4>
			<span class="ama-question-status {{ question.question_status_class }}">{{ question.question_status }}</span>
			{{ question.question_title }}
		</h4>
			<span class="ama-question-actions">
				<?php if ( ask_me_anything_get_option( 'comments_on_questions', true ) ) : ?>
					<span class="ama-number-comments">
						<i class="fa fa-comments"></i> {{ question.number_comments }}
					</span>
				<?php endif; ?>

				<?php if ( $voting != 'none' ) : ?>
					<span class="ama-question-voting">
						<?php if ( $voting == 'all' || $voting == 'up' ) : ?>
							<span class="ama-up-vote">
								<i class="fa fa-thumbs-up"></i> {{ question.number_up }}
							</span>
						<?php endif; ?>
						<?php if ( $voting == 'all' || $voting == 'down' ) : ?>
							<span class="ama-down-vote">
								<i class="fa fa-thumbs-down"></i> {{ question.number_down }}
							</span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</span>
	</a>

	<# }); #>

</script>