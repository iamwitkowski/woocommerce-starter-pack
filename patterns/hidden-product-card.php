<?php
/**
 * Title: Product card
 * Slug: starter-pack/hidden-product-card
 * Inserter: no
 * Description: Product card used inside Product Collection on listings and in related products.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"aspectRatio":"1","className":"product-card__image"} -->
	<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"left"} /-->
<!-- /wp:woocommerce/product-image -->

<!-- wp:group {"className":"product-card__body","style":{"spacing":{"blockGap":"var:preset|spacing|20","padding":{"top":"var:preset|spacing|30"}}},"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group product-card__body" style="padding-top:var(--wp--preset--spacing--30)">
	<!-- wp:post-title {"level":2,"isLink":true,"className":"product-card__title","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
	<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"className":"product-card__price"} /-->
</div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"width":100,"className":"product-card__button"} /-->
