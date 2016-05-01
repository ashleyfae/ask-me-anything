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

	// Add new details meta box.
	add_meta_box( 'ama_question_details', esc_html__( 'Details', 'ask-me-anything' ), 'ask_me_anything_render_details_meta_box', 'question', 'normal', 'high' );
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
 * Render Question Details Meta Box
 *
 * @param WP_Post $post
 *
 * @since 1.0.0
 * @return void
 */
function ask_me_anything_render_details_meta_box( $post ) {
	$question = new AMA_Question( $post->ID );
	?>
	<div class="ama-field">
		<label for="ama_submitter"><?php _e( 'Submitter', 'ask-me-anything' ); ?></label>
		<div class="ama-input-wrapper">
			<input type="text" id="ama_submitter" name="ama_submitter" value="<?php echo esc_attr( $question->get_submitter() ); ?>">
		</div>
	</div>

	<?php do_action( 'ask-me-anything/meta-box/details/after-submitter-field', $post ); ?>

	<div class="ama-field">
		<label for="ama_submitter_email"><?php _e( 'Submitter\'s Email Address', 'ask-me-anything' ); ?></label>
		<div class="ama-input-wrapper">
			<input type="email" id="ama_submitter_email" name="ama_submitter_email" value="<?php echo esc_attr( $question->get_submitter_email() ); ?>">
		</div>
	</div>

	<?php do_action( 'ask-me-anything/meta-box/details/after-submitter-email-field', $post ); ?>

	<div class="ama-field">
		<label for="ama_notify_submitter"><?php _e( 'Notify Submitter', 'ask-me-anything' ); ?></label>
		<div class="ama-input-wrapper">
			<input type="checkbox" id="ama_notify_submitter" name="ama_notify_submitter" value="1" <?php checked( $question->get_notify_submitter(), true ); ?>>
		</div>
	</div>

	<?php do_action( 'ask-me-anything/meta-box/details/after-notify-field', $post ); ?>

	<div class="ama-field">
		<label for="ama_up_votes"><?php _e( 'Up Votes', 'ask-me-anything' ); ?></label>
		<div class="ama-input-wrapper">
			<input type="number" id="ama_up_votes" class="text-small" name="ama_up_votes" value="<?php echo esc_attr( $question->get_up_votes() ); ?>" readonly>
		</div>
	</div>

	<?php do_action( 'ask-me-anything/meta-box/details/after-up-votes-field', $post ); ?>

	<div class="ama-field">
		<label for="ama_down_votes"><?php _e( 'Down Votes', 'ask-me-anything' ); ?></label>
		<div class="ama-input-wrapper">
			<input type="number" id="ama_down_votes" class="text-small" name="ama_down_votes" value="<?php echo esc_attr( $question->get_down_votes() ); ?>" readonly>
		</div>
	</div>

	<?php do_action( 'ask-me-anything/meta-box/details/after-down-votes-field', $post ); ?>

	<?php
	wp_nonce_field( 'ama_save_details_meta', 'ask_me_anything_meta_nonce' );
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

	/*
	 * Permission Check
	 */

	if ( ! isset( $_POST['ask_me_anything_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ask_me_anything_meta_nonce'], 'ama_save_details_meta' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_question', $post_id ) ) {
		return;
	}

	/*
	 * Okay now we can save.
	 */

	$fields = array(
		'ama_submitter',
		'ama_submitter_email',
		'ama_notify_submitter'
	);

	foreach ( apply_filters( 'ask-me-anything/meta-box/saved-fields', $fields ) as $field ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'ask-me-anything/meta-box/sanitize/' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	do_action( 'ask-me-anything/meta-box/save-question', $post_id, $post );
}

add_action( 'save_post', 'ask_me_anything_save_meta', 10, 2 );