<?php
/**
 * Button that triggers the AMA form.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

$button_text = ask_me_anything_get_option( 'button_text', __( 'Ask Me a Question', 'ask-me-anything' ) );
$position    = ask_me_anything_get_option( 'display_position', 'bottom-right' );
?>
<button class="ask-me-anything-button ask-me-anything-button-<?php echo sanitize_html_class( $position ); ?>" data-target="#ask-me-anything"><?php echo $button_text; ?></button>