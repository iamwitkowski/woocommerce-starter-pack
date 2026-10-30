<?php
/**
 * Minimal accessibility fixes for WooCommerce block markup.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

use WP_HTML_Tag_Processor;

defined( 'ABSPATH' ) || exit;

/**
 * Patches ARIA errors found by axe in WooCommerce 11.2 blocks. Each fix only adds or removes one
 * attribute on server-rendered markup, so behaviour and Interactivity API bindings are unchanged.
 */
final class Accessibility {

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/product-filter-checkbox-list', array( $this, 'label_rating_stars_as_image' ) );
		add_filter( 'render_block_woocommerce/add-to-cart-with-options-variation-selector', array( $this, 'remove_variation_list_role' ) );
	}

	/**
	 * Rating filter: the stars <span> carries aria-label without a role (axe: aria-prohibited-attr,
	 * WCAG 4.1.2). role="img" makes the label valid ("Rated 5 out of 5").
	 *
	 * @param string $content Rendered block.
	 */
	public function label_rating_stars_as_image( string $content ): string {
		$html = new WP_HTML_Tag_Processor( $content );
		while ( $html->next_tag( array( 'class_name' => 'wc-block-product-filter-checkbox-list__stars' ) ) ) {
			if ( null !== $html->get_attribute( 'aria-label' ) ) {
				$html->set_attribute( 'role', 'img' );
			}
		}
		return $html->get_updated_html();
	}

	/**
	 * Variation selector: role="list" wraps radiogroups instead of list items (axe:
	 * aria-required-children, WCAG 1.3.1). Each attribute is already a labelled radiogroup, so the
	 * list role adds nothing and is removed.
	 *
	 * @param string $content Rendered block.
	 */
	public function remove_variation_list_role( string $content ): string {
		$html = new WP_HTML_Tag_Processor( $content );
		if ( $html->next_tag() && 'list' === $html->get_attribute( 'role' ) ) {
			$html->remove_attribute( 'role' );
		}
		return $html->get_updated_html();
	}
}
