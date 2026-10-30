<?php
/**
 * Composes theme modules.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every theme module. Holds no logic of its own.
 */
final class Theme {

	/**
	 * Registers all modules' hooks.
	 */
	public function register(): void {
		( new Setup() )->register();
		( new Assets() )->register();
		( new WooCommerce\HookedBlocks() )->register();
		( new WooCommerce\MiniCart() )->register();
		( new WooCommerce\Catalog() )->register();
		( new WooCommerce\Accessibility() )->register();
	}
}
