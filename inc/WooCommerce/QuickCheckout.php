<?php
/**
 * "Quick path to checkout" option.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Optional shorter purchase path: when enabled, the Mini-Cart drawer shows only "Go to checkout"
 * (the "View my cart" button is not rendered). The cart page itself keeps working.
 *
 * Setting: WooCommerce → Settings → Advanced → Theme. Off by default.
 */
final class QuickCheckout {

	public const OPTION = 'starter_pack_quick_checkout';

	private const SECTION = 'starter_pack';

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'woocommerce_get_sections_advanced', array( $this, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( $this, 'add_settings' ), 10, 2 );
		add_filter( 'render_block_woocommerce/mini-cart-cart-button-block', array( $this, 'hide_cart_button' ) );
	}

	/**
	 * Adds the "Theme" section to the Advanced settings tab.
	 *
	 * @param array<string, string> $sections Section id => title.
	 * @return array<string, string>
	 */
	public function add_section( array $sections ): array {
		$sections[ self::SECTION ] = __( 'Theme', 'starter-pack' );
		return $sections;
	}

	/**
	 * Settings shown in the "Theme" section.
	 *
	 * @param array<int, array<string, mixed>> $settings Settings of the current section.
	 * @param string                           $section  Current section id.
	 * @return array<int, array<string, mixed>>
	 */
	public function add_settings( array $settings, string $section ): array {
		if ( self::SECTION !== $section ) {
			return $settings;
		}

		return array(
			array(
				'id'    => self::SECTION,
				'type'  => 'title',
				'title' => __( 'Theme', 'starter-pack' ),
			),
			array(
				'id'       => self::OPTION,
				'type'     => 'checkbox',
				'title'    => __( 'Quick path to checkout', 'starter-pack' ),
				'desc'     => __( 'Skip the cart page: the cart drawer shows only the checkout button.', 'starter-pack' ),
				'desc_tip' => __( 'The cart page stays available, e.g. from the footer or the "Back to cart" link at checkout.', 'starter-pack' ),
				'default'  => 'no',
			),
			array(
				'id'   => self::SECTION,
				'type' => 'sectionend',
			),
		);
	}

	/**
	 * Removes the "View my cart" button from the drawer when the option is on.
	 *
	 * @param string $content Rendered block.
	 */
	public function hide_cart_button( string $content ): string {
		return self::is_enabled() ? '' : $content;
	}

	/**
	 * Whether the quick path is enabled.
	 */
	public static function is_enabled(): bool {
		return 'yes' === get_option( self::OPTION, 'no' );
	}
}
