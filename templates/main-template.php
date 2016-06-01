<?php
/**
 * Main Template
 *
 * General structure of the questions and submission form. This base template includes a variety
 * of other files in certain places. (See below comments.)
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$show_questions = ask_me_anything_get_option( 'show_questions' );
$modal_class    = $show_questions ? ' ask-me-anything-has-questions' : '';
?>

<div id="ask-me-anything" class="ask-me-anything-layout ask-me-anything-popup ask-me-anything-modal<?php echo esc_attr( $modal_class ); ?>">

	<div class="ask-me-anything-modal-inner">

		<?php
		/*
		 * Button for closing the modal window.
		 */
		?>
		<button type="button" class="ama-close-modal" aria-hidden="true">&times;</button>

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

			do_action( 'ask-me-anything/main-template/after-submit-question' );
			?>
		</div>

	</div>

</div>