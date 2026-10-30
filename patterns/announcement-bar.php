<?php
/**
 * Title: Announcement bar
 * Slug: starter-pack/announcement-bar
 * Categories: banner
 * Keywords: announcement, promo, banner
 * Description: Slim bar with a short message and an optional link. Delete the block to turn the bar off.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

$starter_pack_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"metadata":{"name":"<?php echo esc_attr__( 'Announcement bar', 'starter-pack' ); ?>"},"className":"is-style-section-inverted","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"fontSize":"small","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-section-inverted has-small-font-size" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)">
	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><?php echo esc_html__( 'New arrivals are in.', 'starter-pack' ); ?> <a href="<?php echo esc_url( $starter_pack_shop_url ); ?>"><?php echo esc_html__( 'Shop the collection', 'starter-pack' ); ?></a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
