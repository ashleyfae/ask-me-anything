<?php
/**
 * Quick Edit
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

function ask_me_anything_quick_edit_content( $column_name, $post_type ) {
	if ( $post_type != 'question' ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right inline-edit-question">
		<div class="inline-edit-col column-<?php echo $column_name; ?>">
			<label class="inline-edit-group">
				<?php if ( $column_name == 'status' ) : ?>
					<span class="title"><?php _e( 'Status', 'ask-me-anything' ); ?></span>
					<select name="_status">
						<option value="-1"><?php _e( '&mdash; No Change &mdash;', 'ask-me-anything' ); ?></option>
						<?php foreach ( ask_me_anything_get_statuses() as $key => $name ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</label>
		</div>
	</fieldset>
	<?php
}

add_action( 'quick_edit_custom_box', 'ask_me_anything_quick_edit_content', 10, 2 );
add_action( 'bulk_edit_custom_box', 'ask_me_anything_quick_edit_content', 10, 2 );