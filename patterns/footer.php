<?php
/**
 * Title: Footer
 * Slug: starter-pack/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Site title and tagline, store links and copyright.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"site-footer is-style-section-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50"},"margin":{"top":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group site-footer is-style-section-surface" style="margin-top:var(--wp--preset--spacing--70);padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group">
			<!-- wp:site-title {"level":0} /-->
			<!-- wp:site-tagline {"textColor":"muted","fontSize":"small"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:navigation {"overlayMenu":"never","metadata":{"name":"<?php echo esc_attr__( 'Footer navigation', 'starter-pack' ); ?>"},"ariaLabel":"<?php echo esc_attr__( 'Store', 'starter-pack' ); ?>","layout":{"type":"flex","orientation":"vertical"}} -->
			<?php echo StarterPack\Navigation::footer_links(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialize_block() output. ?>
		<!-- /wp:navigation -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--60)">
		<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
		<p class="has-muted-color has-text-color has-small-font-size">© <?php echo esc_html( wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
