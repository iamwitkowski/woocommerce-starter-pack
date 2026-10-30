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
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_base_style' ) );
		add_action( 'after_setup_theme', array( $this, 'add_editor_style' ) );
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
