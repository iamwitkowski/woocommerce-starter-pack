<?php
/**
 * Opts the theme's header out of WooCommerce auto-inserted blocks.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce uses Block Hooks to insert the Mini-Cart and Customer Account
 * blocks after core/navigation in header patterns. The theme header already
 * places both blocks, so the auto-inserted copies would be duplicates.
 */
final class HookedBlocks {

	private const PATTERNS = array( 'starter-pack/header' );

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'woocommerce_hooked_blocks_pattern_exclude_list', array( $this, 'exclude_patterns' ) );
	}

	/**
	 * Adds the theme header pattern to WooCommerce's exclude list.
	 *
	 * @param mixed $patterns Pattern slugs excluded from auto-insertion.
	 * @return mixed
	 */
	public function exclude_patterns( $patterns ) {
		if ( ! is_array( $patterns ) ) {
			return $patterns;
		}
		return array_merge( $patterns, self::PATTERNS );
	}
}
