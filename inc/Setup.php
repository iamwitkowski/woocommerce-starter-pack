<?php
/**
 * Core theme setup.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack;

defined( 'ABSPATH' ) || exit;

/**
 * Theme setup that does not belong to a specific feature.
 */
final class Setup {

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'after_setup_theme', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Loads translations bundled with the theme.
	 */
	public function load_textdomain(): void {
		load_theme_textdomain( 'starter-pack', get_template_directory() . '/languages' );
	}
}
