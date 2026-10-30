<?php
/**
 * Catalog (listing) defaults.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Listing defaults that fit the theme's product grid.
 */
final class Catalog {

	/**
	 * Divisible by 2, 3 and 4, so the responsive grid never ends with a lone card.
	 */
	private const PRODUCTS_PER_PAGE = 12;

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'loop_shop_per_page', array( $this, 'products_per_page' ), 5 );
	}

	/**
	 * Products per page on shop, taxonomy and product search pages. Runs early (priority 5) so
	 * plugins and child themes can still override it.
	 */
	public function products_per_page(): int {
		return self::PRODUCTS_PER_PAGE;
	}
}
