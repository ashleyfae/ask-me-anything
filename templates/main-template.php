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

$position = ask_me_anything_get_option( 'display_position', 'bottom-right' );
?>

<div class="ask-me-anything-layout ask-me-anything-<?php echo sanitize_html_class( $position ); ?>">

	<?php ask_me_anything_get_template_part( 'questions' ); ?>

	<?php ask_me_anything_get_template_part( 'submit-question', 'form' ); ?>

</div>
