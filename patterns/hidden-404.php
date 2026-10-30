<?php
/**
 * Title: 404
 * Slug: starter-pack/hidden-404
 * Inserter: no
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

$starter_pack_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html__( 'Page not found', 'starter-pack' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'The page you are looking for does not exist or has been moved. Search the store or go back to the shop.', 'starter-pack' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search products', 'starter-pack' ); ?>","buttonText":"<?php echo esc_attr__( 'Search', 'starter-pack' ); ?>","query":{"post_type":"product"}} /-->

<!-- wp:buttons -->
<div class="wp-block-buttons">
	<!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $starter_pack_shop_url ); ?>"><?php echo esc_html__( 'Go to the shop', 'starter-pack' ); ?></a></div>
	<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
