<?php
/**
 * Meta Box
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Register and Remove Meta Boxes
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_meta_boxes() {
	// Remove normal publish meta box.
	remove_meta_box( 'submitdiv', 'question', 'side' );

	// Add new publish meta box.
	add_meta_box( 'ama_question_submit', esc_html__( 'Save', 'ask-me-anything' ), 'ask_me_anything_render_new_publish_meta_box', 'question', 'side', 'high' );
}

add_action( 'add_meta_boxes', 'ask_me_anything_meta_boxes' );

/**
 * Render Publish Meta Box
 *
 * @param WP_Post $post
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_render_new_publish_meta_box( $post ) {
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish      = current_user_can( $post_type_object->cap->publish_posts );
	$ama_statuses     = ask_me_anything_get_statuses();

	var_dump( $post->post_status );
	?>
	<div class="submitbox" id="submitpost">
		<div id="minor-publishing-actions">
			<label for="ama_status" class="screen-reader-text"><?php _e( 'Select a status', 'ask-me-anything' ); ?></label>
			<select id="ama_status" name="ama_status">
				<?php foreach ( $ama_statuses as $key => $name ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $post->post_status, $key ); ?>><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select>

			<?php do_action( 'ask-me-anything/meta-box/publish/after-status', $post ); ?>
		</div>

		<div id="major-publishing-actions">
			<div id="delete-action">
				<?php
				if ( current_user_can( "delete_post", $post->ID ) ) {
					if ( ! EMPTY_TRASH_DAYS ) {
						$delete_text = __( 'Delete Permanently', 'ask-me-anything' );
					} else {
						$delete_text = __( 'Move to Trash', 'ask-me-anything' );
					}
					?>
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a><?php
				} ?>
			</div>

			<div id="publishing-action">
				<span class="spinner"></span>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php echo esc_attr( $post->post_status ); ?>"/>
				<?php
				if ( $can_publish ) : ?>
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Save' ) ?>"/>
				<?php else : ?>
					<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false ); ?>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php
}

/**
 * Save Post Meta
 *
 * @param int     $post_id
 * @param WP_Post $post
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_save_meta( $post_id, $post ) {

	// @todo
}

add_action( 'save_post', 'ask_me_anything_save_meta', 10, 2 );