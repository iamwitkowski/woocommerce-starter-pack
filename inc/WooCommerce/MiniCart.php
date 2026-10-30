<?php
/**
 * Mini-Cart behaviour.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

use WP_HTML_Tag_Processor;

defined( 'ABSPATH' ) || exit;

/**
 * Adjusts the native WooCommerce Mini-Cart block.
 */
final class MiniCart {

	private const FOCUS_SCRIPT        = 'assets/js/mini-cart-focus.js';
	private const FOCUS_SCRIPT_HANDLE = 'starter-pack-mini-cart-focus';

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/mini-cart', array( $this, 'remove_on_cart_and_checkout' ) );
		add_filter( 'render_block_woocommerce/mini-cart', array( $this, 'enqueue_focus_script' ) );
		add_action( 'wp_footer', array( $this, 'wrap_overlay' ), 9 );
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

	/**
	 * Loads the focus-return script only on pages that render the Mini-Cart.
	 *
	 * @param string $content Rendered block.
	 */
	public function enqueue_focus_script( string $content ): string {
		if ( '' !== $content ) {
			wp_enqueue_script(
				self::FOCUS_SCRIPT_HANDLE,
				get_theme_file_uri( self::FOCUS_SCRIPT ),
				array(),
				(string) wp_get_theme()->get( 'Version' ),
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
		}
		return $content;
	}

	/**
	 * WooCommerce echoes the drawer in wp_footer (priority 10) without a filter. This swaps that one
	 * callback for a wrapper that adds an accessible name to the dialog.
	 */
	public function wrap_overlay(): void {
		global $wp_filter;
		if ( ! isset( $wp_filter['wp_footer']->callbacks[10] ) ) {
			return;
		}
		foreach ( $wp_filter['wp_footer']->callbacks[10] as $callback ) {
			$function = $callback['function'];
			if ( is_array( $function ) && 'render_mini_cart_overlay' === $function[1] ) {
				remove_action( 'wp_footer', $function, 10 );
				add_action(
					'wp_footer',
					function () use ( $function ) {
						ob_start();
						call_user_func( $function );
						echo $this->name_drawer( (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce markup, one attribute added by WP_HTML_Tag_Processor.
					},
					10
				);
				return;
			}
		}
	}

	/**
	 * The drawer has role="dialog" when open but no accessible name (WCAG 4.1.2).
	 *
	 * @param string $html Overlay markup.
	 */
	public function name_drawer( string $html ): string {
		$tags = new WP_HTML_Tag_Processor( $html );
		if ( $tags->next_tag( array( 'class_name' => 'wc-block-mini-cart__drawer' ) ) ) {
			$tags->set_attribute( 'aria-label', __( 'Shopping cart', 'starter-pack' ) );
		}
		return $tags->get_updated_html();
	}
}
