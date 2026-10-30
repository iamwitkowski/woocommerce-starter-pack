<?php
/**
 * Front-end and editor styles.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the global base stylesheet on the front end and in the editor.
 */
final class Assets {

	private const BASE_STYLE        = 'assets/css/base.css';
	private const BASE_STYLE_HANDLE = 'starter-pack-base';

	/**
	 * Per-block stylesheets in assets/css/blocks/, loaded only on pages that render the block.
	 */
	private const BLOCK_STYLES = array(
		'woocommerce/mini-cart' => 'woocommerce-mini-cart',
	);

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_base_style' ) );
		add_action( 'after_setup_theme', array( $this, 'add_editor_style' ) );
		add_action( 'init', array( $this, 'enqueue_block_styles' ) );
	}

	/**
	 * Enqueues base.css. The "path" data lets WordPress inline it while it stays under the inline size limit.
	 */
	public function enqueue_base_style(): void {
		wp_enqueue_style(
			self::BASE_STYLE_HANDLE,
			get_theme_file_uri( self::BASE_STYLE ),
			array(),
			$this->version()
		);
		wp_style_add_data( self::BASE_STYLE_HANDLE, 'path', get_theme_file_path( self::BASE_STYLE ) );
	}

	/**
	 * Attaches per-block stylesheets. WordPress inlines them (via "path") and prints them only when the block is rendered.
	 */
	public function enqueue_block_styles(): void {
		foreach ( self::BLOCK_STYLES as $block => $file ) {
			$path = 'assets/css/blocks/' . $file . '.css';
			wp_enqueue_block_style(
				$block,
				array(
					'handle' => 'starter-pack-' . $file,
					'src'    => get_theme_file_uri( $path ),
					'path'   => get_theme_file_path( $path ),
					'ver'    => $this->version(),
				)
			);
		}
	}

	/**
	 * Loads base.css inside the block editor canvas so the editor matches the front end.
	 */
	public function add_editor_style(): void {
		add_editor_style( self::BASE_STYLE );
	}

	/**
	 * Theme version used for cache busting.
	 */
	private function version(): string {
		return (string) wp_get_theme()->get( 'Version' );
	}
}
