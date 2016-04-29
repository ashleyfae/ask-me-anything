<?php
/**
 * Questions
 *
 * Template part for displaying the list of questions.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Do not show if the questions are disabled in the settings.
if ( ! ask_me_anything_get_option( 'show_questions', true ) ) {
	return;
}

?>
<div class="ask-me-anything-questions-list">

	<?php
	/*
	 * @see single-question.php
	 * That template gets pulled inside here and repeated for each individual question.
	 */
	?>

</div>
