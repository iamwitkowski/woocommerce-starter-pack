<?php
/**
 * Title: Posts grid
 * Slug: starter-pack/hidden-posts-grid
 * Inserter: no
 * Description: Query loop that inherits the current archive, search or blog query.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:query {"queryId":0,"query":{"inherit":true},"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-query alignwide">
	<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"18rem"}} -->
		<!-- wp:group {"tagName":"article","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
		<article class="wp-block-group">
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","sizeSlug":"medium_large","style":{"border":{"radius":"var(--wp--custom--radius--card)"}}} /-->
			<!-- wp:post-date /-->
			<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"large"} /-->
			<!-- wp:post-excerpt {"excerptLength":24} /-->
		</article>
		<!-- /wp:group -->
	<!-- /wp:post-template -->

	<!-- wp:query-pagination {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->

	<!-- wp:query-no-results -->
		<!-- wp:pattern {"slug":"starter-pack/hidden-no-results"} /-->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
