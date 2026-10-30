<?php
/**
 * Mini-Cart behaviour.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Adjusts the native WooCommerce Mini-Cart block.
 */
final class MiniCart {

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/mini-cart', array( $this, 'remove_on_cart_and_checkout' ) );
	}

	/**
	 * Drops the Mini-Cart on the cart and checkout pages.
	 *
	 * There it would only duplicate the page itself, and in WooCommerce 11.2 its non-interactive
	 * fallback leaves the shared cart store without a REST URL, which requests
	 * "undefinedwc/store/v1/cart" (404).
	 *
	 * @param string $content Rendered block.
	 */
	public function remove_on_cart_and_checkout( string $content ): string {
		return is_cart() || is_checkout() ? '' : $content;
	}
}
