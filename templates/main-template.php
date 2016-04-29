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

		<?php ask_me_anything_get_template_part( 'questions' ); ?>

		<div class="ask-me-anything-submit-question">
			<?php
			/*
			 * @see submit-question-form.php
			 * That template gets pulled inside here automatically.
			 */
			?>
			<?php //ask_me_anything_get_template_part( 'submit-question', 'form' ); ?>
		</div>

	</div>

</div>
