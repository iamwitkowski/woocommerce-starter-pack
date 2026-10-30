<?php
/**
 * Title: Header
 * Slug: starter-pack/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Announcement bar, site title, navigation with product search, account and mini-cart.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:pattern {"slug":"starter-pack/announcement-bar"} /-->

<!-- wp:group {"className":"site-header","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group site-header" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">
			<!-- wp:site-logo {"width":40,"shouldSyncIcon":false} /-->
			<!-- wp:site-title {"level":0} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
		<div class="wp-block-group">
			<!-- wp:navigation {"overlayBackgroundColor":"background","overlayTextColor":"text","metadata":{"name":"<?php echo esc_attr__( 'Header navigation', 'starter-pack' ); ?>"},"layout":{"type":"flex","justifyContent":"right"}} -->
				<?php echo StarterPack\Navigation::header_links(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialize_block() output. ?>
				<!-- wp:search {"label":"<?php echo esc_attr__( 'Search products', 'starter-pack' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr__( 'Search products…', 'starter-pack' ); ?>","buttonText":"<?php echo esc_attr__( 'Search', 'starter-pack' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"product"}} /-->
			<!-- /wp:navigation -->

			<!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","iconStyle":"line","iconClass":"wc-block-customer-account__account-icon"} /-->

			<!-- wp:woocommerce/mini-cart {"addToCartBehaviour":"open_drawer"} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
