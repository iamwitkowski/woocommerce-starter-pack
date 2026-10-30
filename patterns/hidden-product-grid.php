<?php
/**
 * Title: Product grid
 * Slug: starter-pack/hidden-product-grid
 * Inserter: no
 * Description: Product Collection that inherits the current shop, taxonomy or product search query.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"taxQuery":{},"isProductCollectionBlock":true,"perPage":12,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":true},"tagName":"div","displayLayout":{"type":"flex","columns":3,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"className":"product-grid"} -->
<div class="wp-block-woocommerce-product-collection product-grid">
	<!-- wp:woocommerce/product-template {"className":"product-cards"} -->
		<!-- wp:pattern {"slug":"starter-pack/hidden-product-card"} /-->
	<!-- /wp:woocommerce/product-template -->

	<!-- wp:query-pagination {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->

	<!-- wp:woocommerce/product-collection-no-results -->
		<!-- wp:pattern {"slug":"starter-pack/hidden-no-products"} /-->
	<!-- /wp:woocommerce/product-collection-no-results -->
</div>
<!-- /wp:woocommerce/product-collection -->
