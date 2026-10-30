<?php
/**
 * Title: Comments
 * Slug: starter-pack/hidden-comments
 * Inserter: no
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:comments {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<div class="wp-block-comments" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:comments-title {"level":2,"fontSize":"x-large"} /-->

	<!-- wp:comment-template -->
		<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--50)">
			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group">
				<!-- wp:avatar {"size":40,"style":{"border":{"radius":"var:preset|border-radius|full"}}} /-->
				<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group">
					<!-- wp:comment-author-name {"style":{"typography":{"fontWeight":"600"}}} /-->
					<!-- wp:comment-date {"textColor":"muted","fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
			<!-- wp:comment-content /-->
			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex"},"fontSize":"small"} -->
			<div class="wp-block-group has-small-font-size">
				<!-- wp:comment-reply-link /-->
				<!-- wp:comment-edit-link /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	<!-- /wp:comment-template -->

	<!-- wp:comments-pagination -->
		<!-- wp:comments-pagination-previous /-->
		<!-- wp:comments-pagination-numbers /-->
		<!-- wp:comments-pagination-next /-->
	<!-- /wp:comments-pagination -->

	<!-- wp:post-comments-form /-->
</div>
<!-- /wp:comments -->
