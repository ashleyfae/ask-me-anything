<?php
/**
 * Main Template
 *
 * General structure of the questions and submission form.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

?>

<div id="ask-me-anything" class="ask-me-anything-layout ask-me-anything-modal">

	<div class="ask-me-anything-modal-inner">

		<?php
		/*
		 * List of questions on the right.
		 */
		if ( ask_me_anything_get_option( 'show_questions', true ) ) : ?>
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
