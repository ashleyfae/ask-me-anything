<?php
/**
 * Comments Template - Each comment is given this layout.
 * (Underscores JS Template)
 *
 * The following values are available for each comment:
 *
 *  + comment.ID - ID number of the comment.
 *  + comment.avatar - Formatted <img> tag with the avatar.
 *  + comment.comment_author - Name of the commenter.
 *  + comment.comment_author_email - Email address of the commenter (not recommended for public use!).
 *  + comment.comment_author_url - Website URL of the commenter.
 *  + comment.comment_date - Date the comment was submitted.
 *  + comment.comment_content - Actual comment message.
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

?>
<script id="tmpl-ama-comments" type="text/html">

<# _.each( data.comments, function( comment ) { #>

	<div class="ama-question-comment">

		{{{ comment.avatar }}}

		<div class="ama-question-comment-content">

			<h4>{{ comment.comment_author }}</h4>

			{{{ comment.comment_content }}}

		</div>

	</div>

<# }); #>

</script>