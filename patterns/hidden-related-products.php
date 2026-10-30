<?php
/**
 * Title: Related products
 * Slug: starter-pack/hidden-related-products
 * Inserter: no
 * Description: Related products in the theme product card.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[]},"tagName":"section","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/related","hideControls":["inherit"],"queryContextIncludes":["collection"],"align":"wide","className":"related-products","style":{"spacing":{"margin":{"top":"var:preset|spacing|70"}}}} -->
<section class="wp-block-woocommerce-product-collection alignwide related-products" style="margin-top:var(--wp--preset--spacing--70)">
	<!-- wp:heading {"fontSize":"x-large","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<h2 class="wp-block-heading has-x-large-font-size" style="margin-bottom:var(--wp--preset--spacing--50)"><?php echo esc_html__( 'You may also like', 'starter-pack' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:woocommerce/product-template {"className":"product-cards"} -->
		<!-- wp:pattern {"slug":"starter-pack/hidden-product-card"} /-->
	<!-- /wp:woocommerce/product-template -->
</section>
<!-- /wp:woocommerce/product-collection -->
